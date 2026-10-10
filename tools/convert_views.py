#!/usr/bin/env python3
"""
Convert legacy (AdminLTE / Bootstrap 4) Blade views to the Attex (Bootstrap 5) shell.

Usage (from project root):
    python3 tools/convert_views.py resources/views/vehicles resources/views/drivers
    python3 tools/convert_views.py --dry-run resources/views/reports

Only files that contain  @extends('layouts.app')  are touched. Line endings are preserved.
It is idempotent: running it twice changes nothing the second time.

What it does
  * @extends layouts.app            -> layouts.attex
  * @section('script2')             -> @section('scripts')      (attex has no script2 yield)
  * @section('heading')             -> @section('page_title')   (and <h1> -> <span>)
  * data-toggle/target/dismiss     -> data-bs-*  (only for Bootstrap components)
  * data-placement                 -> data-bs-placement
  * modal close "x" buttons        -> <button class="btn-close" ...>
  * class tokens BS4 -> BS5        (float-right, ml-2, text-right, badge-*, custom-select, ...)
  * card-info/primary/... colours  -> plain card
NOT touched: JavaScript logic, Blade logic, input-group-append/prepend, form-group, custom-control
(these still render acceptably; convert by hand when you open the page).
"""
import re, sys, os

BS_TOGGLES = {'modal','tab','pill','tooltip','popover','collapse','dropdown'}
CLASS_MAP = {
    'float-right':'float-end','float-left':'float-start',
    'text-right':'text-end','text-left':'text-start',
    'custom-select':'form-select','form-row':'row','no-gutters':'g-0',
    'sr-only':'visually-hidden','font-weight-bold':'fw-bold','font-weight-normal':'fw-normal',
    'font-weight-light':'fw-light','font-italic':'fst-italic','text-monospace':'font-monospace',
    'badge-pill':'rounded-pill','btn-default':'btn-light','close':'btn-close',
    'embed-responsive':'ratio','border-left':'border-start','border-right':'border-end',
    'rounded-left':'rounded-start','rounded-right':'rounded-end',
}
CLASS_MAP_SKIP = {'close'}  # handled by the button rewrite, not by token map
SPACING = re.compile(r'^(m|p)(l|r)-((sm|md|lg|xl)-)?(-?\d|auto)$')
BADGE = re.compile(r'^badge-(primary|secondary|success|danger|warning|info|light|dark)$')
CARD_COLOR = re.compile(r'^card-(info|primary|success|warning|danger|secondary|outline)$')

def map_class_value(val):
    out = []
    for tok in val.split(' '):
        if not tok or '{' in tok or '@' in tok or '$' in tok:
            out.append(tok); continue
        if tok in CLASS_MAP and tok not in CLASS_MAP_SKIP:
            out.append(CLASS_MAP[tok]); continue
        m = SPACING.match(tok)
        if m:
            side = 's' if m.group(2) == 'l' else 'e'
            out.append(tok[0] + side + tok[2:]); continue
        m = BADGE.match(tok)
        if m:
            out.append('bg-' + m.group(1)); continue
        if CARD_COLOR.match(tok):
            continue  # drop AdminLTE card colour modifier
        out.append(tok)
    return ' '.join(out)

def fix_classes(s):
    def repl(m):
        return m.group(1) + map_class_value(m.group(2)) + m.group(3)
    return re.sub(r'(\bclass\s*=\s*")([^"]*)(")', repl, s)

TAG = re.compile(r'<(?:a|button|div|span|li|i|input|label|img|p|th|td)\b[^<]*?>', re.S)

def fix_data_attrs(s):
    def tag_repl(m):
        t = m.group(0)
        tog = re.search(r'\bdata-toggle\s*=\s*["\']([^"\']+)["\']', t)
        if tog and tog.group(1) in BS_TOGGLES:
            t = re.sub(r'\bdata-toggle\b', 'data-bs-toggle', t)
            t = re.sub(r'\bdata-target\b', 'data-bs-target', t)
            t = re.sub(r'\bdata-placement\b', 'data-bs-placement', t)
            t = re.sub(r'\bdata-parent\b', 'data-bs-parent', t)
        dis = re.search(r'\bdata-dismiss\s*=\s*["\'](modal|alert|toast|offcanvas)["\']', t)
        if dis:
            t = re.sub(r'\bdata-dismiss\b', 'data-bs-dismiss', t)
        return t
    return TAG.sub(tag_repl, s)

CLOSE_BTN = re.compile(
    r'<button\b([^>]*?)\bclass\s*=\s*"close"([^>]*?)>\s*(?:<span[^>]*>\s*)?(?:&times;|×)\s*(?:</span>)?\s*</button>', re.S)

def fix_close_buttons(s):
    def repl(m):
        attrs = (m.group(1) + m.group(2))
        attrs = re.sub(r'\bdata-dismiss\b', 'data-bs-dismiss', attrs)
        attrs = re.sub(r'\baria-label\s*=\s*"[^"]*"', '', attrs)
        attrs = re.sub(r'\s+', ' ', attrs).strip()
        return '<button class="btn-close" aria-label="Close"' + (' ' + attrs if attrs else '') + '></button>'
    return CLOSE_BTN.sub(repl, s)

def convert(src):
    s = src
    if not re.search(r"@extends\(\s*['\"]layouts\.app['\"]\s*\)", s):
        return s
    s = re.sub(r"@extends\(\s*['\"]layouts\.app['\"]\s*\)", "@extends('layouts.attex')", s)
    s = re.sub(r"@section\(\s*['\"]script2['\"]", "@section('scripts'", s)
    # heading -> page_title
    def heading(m):
        body = m.group(0).replace("'heading'", "'page_title'").replace('"heading"', "'page_title'")
        return re.sub(r'<(/?)h1\b[^>]*>', r'<\1span>', body)
    s = re.sub(r"@section\(\s*['\"]heading['\"]\s*\).*?@endsection", heading, s, flags=re.S)
    s = fix_close_buttons(s)
    s = fix_data_attrs(s)
    s = fix_classes(s)
    return s

def main():
    args = [a for a in sys.argv[1:] if not a.startswith('--')]
    dry = '--dry-run' in sys.argv
    if not args:
        print(__doc__); sys.exit(1)
    changed = 0
    for root in args:
        paths = [root] if os.path.isfile(root) else [
            os.path.join(dp, f) for dp, _, fs in os.walk(root) for f in fs if f.endswith('.blade.php')]
        for p in sorted(paths):
            with open(p, newline='', encoding='utf-8') as fh:
                src = fh.read()
            out = convert(src)
            if out != src:
                changed += 1
                print(('would convert ' if dry else 'converted ') + p)
                if not dry:
                    with open(p, 'w', newline='', encoding='utf-8') as fh:
                        fh.write(out)
    print(f'{changed} file(s) {"would change" if dry else "changed"}')

if __name__ == '__main__':
    main()
