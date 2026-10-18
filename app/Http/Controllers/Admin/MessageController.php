<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', ContactMessage::class);

        $status = $request->query('status');

        $messages = ContactMessage::query()
            ->when($request->query('q'), fn ($q, $term) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('subject', 'like', "%{$term}%")))
            // Archived messages are hidden from the default view.
            ->when($status, fn ($q) => $q->where('status', $status), fn ($q) => $q->where('status', '!=', 'archived'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.messages.index', [
            'messages' => $messages,
            'counts' => ContactMessage::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function show(ContactMessage $message): View
    {
        $this->authorize('view', $message);

        if ($message->status === 'new') {
            $message->update(['status' => 'read', 'read_at' => now()]);
        }

        return view('admin.messages.show', compact('message'));
    }

    public function update(Request $request, ContactMessage $message): RedirectResponse
    {
        $this->authorize('update', $message);

        $status = $request->validate(['status' => ['required', 'in:new,read,archived']])['status'];

        $message->update([
            'status' => $status,
            'read_at' => $status === 'new' ? null : ($message->read_at ?? now()),
        ]);

        return $status === 'archived'
            ? redirect()->route('admin.messages.index')->with('status', 'Message archived.')
            : back()->with('status', 'Message marked as '.$status.'.');
    }

    public function destroy(ContactMessage $message): RedirectResponse
    {
        $this->authorize('delete', $message);

        $message->delete();

        return redirect()->route('admin.messages.index')->with('status', 'Message deleted.');
    }
}
