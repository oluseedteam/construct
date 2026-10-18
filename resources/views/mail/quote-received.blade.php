<x-mail::message>
# Thank you, {{ $quote->name }}

We have received your quotation request and our team will review it shortly.

**Your reference number:** {{ $quote->reference }}

Please quote this number if you contact us about your request.

<x-mail::panel>
**Project type:** {{ $quote->project_type }}
**Location:** {{ $quote->project_location }}
</x-mail::panel>

Kind regards,<br>
{{ setting('company_name') }}
</x-mail::message>
