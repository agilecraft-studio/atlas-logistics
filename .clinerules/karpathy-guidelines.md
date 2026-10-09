# AI Coding Discipline (Karpathy-Inspired Guidelines)

Adapted for Atlas Logistics from the public "andrej-karpathy-skills" behavioral guidelines
(multica-ai/andrej-karpathy-skills, MIT). Imported 2026-10-08.

Scope: how the AI assistant behaves while working in this repository.
Purpose: reduce the most common LLM coding mistakes - unstated assumptions, over-engineering,
drive-by refactoring, and unverified claims.

These are behavioural rules only. They never override the domain, security, data-integrity
or business rules in `.clinerules.md`.

## Precedence
When these guidelines appear to conflict with `.clinerules.md`, the Atlas rules win:

| Conflict | Resolution |
| --- | --- |
| "No configurability that wasn't requested" vs "Do not hard-code countries, commodities, capacities, routes" | Business/reference data MUST stay configurable (statuses, commodities, currencies, vehicle types, countries, borders, service intervals, notification thresholds). Speculative flexibility in *code* (unused interfaces, unused service providers, options no screen reads) is still forbidden. |
| "If something is unclear, stop and ask" vs "never guess schema/routes/permissions" | Both point the same way: stop, ask, or read the repository. Never invent a table, column, route, permission or workflow. |
| "Simplest possible code" vs "centralize calculations such as trip profitability" | Simple code, one authoritative location for each calculation. Simplicity is not a licence to duplicate profit/fuel/maintenance-cost maths in controllers. |

## 1. Think Before Coding
Do not assume. Do not hide confusion. Surface tradeoffs.

Before implementing:
- State assumptions explicitly in the plan. If uncertain, ask before editing.
- If a requirement has several valid interpretations (e.g. "vehicle is unavailable"), present them - do not pick one silently.
- If a simpler implementation exists, say so and recommend it, even when it contradicts the request.
- If something is unclear, stop. Name exactly what is confusing, then ask.
- Push back when a request would break authorization, corrupt financial history, or run destructive SQL in production.

## 2. Simplicity First
Minimum code that solves the problem. Nothing speculative.

- No features beyond what was asked.
- No abstractions for single-use code.
- No "flexibility" or "configurability" that was not requested (except project-mandated reference data - see Precedence).
- No error handling for impossible scenarios; do handle the real ones (invalid odometer, negative fuel quantity, duplicate driver assignment, expired licence).
- If 200 lines could be 50, rewrite it before presenting the change.

Ask: "Would a senior Laravel engineer say this is overcomplicated?" If yes, simplify.

## 3. Surgical Changes
Touch only what you must. Clean up only your own mess.

When editing existing code:
- Do not "improve" adjacent code, comments, or formatting.
- Do not refactor things that are not broken (legacy `Form::` / `HTML::` usage stays until an explicit migration task).
- Match existing style, even if it would be done differently today.
- If unrelated dead code is noticed, mention it - do not delete it.

When a change creates orphans:
- Remove only the imports/variables/methods that *this* change made unused.
- Do not remove pre-existing dead code unless asked.

The test: every changed line must trace directly back to the user's request.

## 4. Goal-Driven Execution
Define success criteria, then loop until verified.

Transform imperative tasks into verifiable goals:
- "Add validation" -> "Write tests for invalid inputs, then make them pass."
- "Fix the bug" -> "Write a test that reproduces it, then make it pass."
- "Change the trip status flow" -> "Verify the new status against controlled values, existing trips, and reports before and after."

For multi-step tasks, state a brief plan:

```
1. [Step] -> verify: [check]
2. [Step] -> verify: [check]
3. [Step] -> verify: [check]
```

Weak criteria ("make it work") demand constant clarification and invite invented schema. Strong criteria allow the work to be finished and proven.

## 5. Honest Reporting
- Never claim a test, migration, deployment, SSL install, or email configuration succeeded unless it was actually run and observed.
- Report what changed, what was verified, and what remains unknown - in that order.
- If a verification step was skipped or unavailable, say so explicitly instead of implying success.

## Signals that these guidelines are working
- Diffs contain only requested changes; no drive-by refactors.
- Code is simple on the first attempt, not after a rewrite.
- Clarifying questions arrive before implementation, not after mistakes.
- No unverified success claims, and no guessed tables, columns, routes, or permissions.
