<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/** Staff accounts. Admins only. There is no public registration. */
class UserController extends Controller
{
    public function index(): View
    {
        $this->authorize('manage-settings');

        return view('admin.users.index', ['users' => User::orderBy('role')->orderBy('name')->get()]);
    }

    public function create(): View
    {
        $this->authorize('manage-settings');

        return view('admin.users.form', ['user' => new User(['role' => User::ROLE_EDITOR, 'is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manage-settings');

        $data = $request->validate($this->rules());
        $data['is_active'] = $request->boolean('is_active');

        User::create($data);

        return redirect()->route('admin.users.index')->with('status', 'Staff account created.');
    }

    public function edit(User $user): View
    {
        $this->authorize('manage-settings');

        return view('admin.users.form', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manage-settings');

        $data = $request->validate($this->rules($user));
        $data['is_active'] = $request->boolean('is_active');

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        // Never let the dashboard be left without an active administrator.
        if ($this->wouldRemoveLastAdmin($user, $data['role'], $data['is_active'])) {
            return back()->withInput()->with('error', 'There must always be at least one active administrator.');
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('status', 'Account updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manage-settings');

        if ($user->is($request->user())) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($this->wouldRemoveLastAdmin($user, User::ROLE_EDITOR, false)) {
            return back()->with('error', 'There must always be at least one active administrator.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'Account deleted.');
    }

    private function rules(?User $user = null): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user?->id)],
            'role' => ['required', 'in:'.User::ROLE_ADMIN.','.User::ROLE_EDITOR],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::min(12)->letters()->numbers()],
        ];
    }

    private function wouldRemoveLastAdmin(User $user, string $newRole, bool $newActive): bool
    {
        $stillAdmin = $newRole === User::ROLE_ADMIN && $newActive;

        if (! $user->isAdmin() || ! $user->is_active || $stillAdmin) {
            return false;
        }

        return User::where('role', User::ROLE_ADMIN)->where('is_active', true)->where('id', '!=', $user->id)->doesntExist();
    }
}
