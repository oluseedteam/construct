<x-mail::message>
# Thank you, {{ $application->name }}

We have received your application for **{{ $application->position }}**. Our team will review it and be in touch if there is a fit.

**Your reference number:** {{ $application->reference }}

Kind regards,<br>
{{ setting('company_name') }}
</x-mail::message>
