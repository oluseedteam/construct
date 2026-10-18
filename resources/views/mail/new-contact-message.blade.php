<x-mail::message>
# New website message

**From:** {{ $contact->name }} ({{ $contact->email }})
@if ($contact->phone)
**Phone:** {{ $contact->phone }}
@endif
**Subject:** {{ $contact->subject }}

<x-mail::panel>
{{ $contact->message }}
</x-mail::panel>

<x-mail::button :url="route('admin.messages.show', $contact)">
Open in dashboard
</x-mail::button>

You can reply directly to this email to answer {{ $contact->name }}.
</x-mail::message>
