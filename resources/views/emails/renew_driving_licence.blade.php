@component('mail::message')
# Renew Driving Licence

Dear {{$user}},

{!! AtlasLogistics::email_msg('driving_licence') !!}
@php($date_format_setting=(AtlasLogistics::get('date_format'))?AtlasLogistics::get('date_format'):'d-m-Y')
	Driver:	{{$driver}}
	Licence Expiry date: {{date($date_format_setting,strtotime($lic_date))}}
	Remaining days: {{$diff_in_days}}




Thanks,<br>
{{ config('app.name') }}
@endcomponent
