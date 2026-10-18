<x-mail::message>
# New quotation request

**Reference:** {{ $quote->reference }}

<x-mail::table>
| | |
|:--|:--|
| Customer | {{ $quote->name }} |
| Email | {{ $quote->email }} |
| Phone | {{ $quote->phone }} |
| Project type | {{ $quote->project_type }} |
| Location | {{ $quote->project_location }} |
| Budget | {{ $quote->estimated_budget ?: 'Not specified' }} |
| Preferred start | {{ $quote->preferred_start_date?->format('M j, Y') ?: 'Not specified' }} |
| Document | {{ $quote->document_name ? 'Attached (view in dashboard)' : 'None' }} |
</x-mail::table>

<x-mail::panel>
{{ $quote->description }}
</x-mail::panel>

<x-mail::button :url="route('admin.quotes.show', $quote)">
Review request
</x-mail::button>
</x-mail::message>
