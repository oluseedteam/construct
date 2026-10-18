<x-mail::message>
# Re: your quotation request

Hello {{ $quote->name }},

{!! nl2br(e($replyBody)) !!}

<x-mail::subcopy>
Reference: {{ $quote->reference }}
</x-mail::subcopy>

Kind regards,<br>
{{ setting('company_name') }}
</x-mail::message>
