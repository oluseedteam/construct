<x-mail::message>
# New job application

**Reference:** {{ $application->reference }}

<x-mail::table>
| | |
|:--|:--|
| Position | {{ $application->position }} |
| Applicant | {{ $application->name }} |
| Email | {{ $application->email }} |
| Phone | {{ $application->phone }} |
| City | {{ $application->city ?: 'Not specified' }} |
| Experience | {{ $application->experience ?: 'Not specified' }} |
| Available from | {{ $application->available_from?->format('M j, Y') ?: 'Not specified' }} |
</x-mail::table>

@if ($application->cover_letter)
<x-mail::panel>
{{ $application->cover_letter }}
</x-mail::panel>
@endif

<x-mail::button :url="route('admin.applications.show', $application)">
Review application and resume
</x-mail::button>
</x-mail::message>
