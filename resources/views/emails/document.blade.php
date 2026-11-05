<x-mail::message>
# {{ $documentLabel }} {{ $documentNumber }}

Hello {{ $customerName }},

{{ $intro ?: 'Please find your ' . strtolower($documentLabel) . ' attached to this email.' }}

@if($attachmentName)
The document is attached as **{{ $attachmentName }}**.
@endif

If you have any questions, reply to this email and our team will assist.

Thanks,<br>
{{ \App\Model\AtlasLogistics::get('app_name') ?: config('app.name') }}
</x-mail::message>
