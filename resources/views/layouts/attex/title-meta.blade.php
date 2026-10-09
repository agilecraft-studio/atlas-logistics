<meta charset="utf-8" />
<title>{{ ($page_title ?? '') !== '' ? ($page_title ?? '') . ' | ' : '' }}{{ AtlasLogistics::get('app_name') }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta content="Atlas Logistics Fleet & Transport Management System" name="description" />
<link rel="manifest" href="{{ asset('web-manifest.json?v2') }}">
<link rel="icon" href="{{ asset('assets/images/' . AtlasLogistics::get('icon_img')) }}" type="image/png">
