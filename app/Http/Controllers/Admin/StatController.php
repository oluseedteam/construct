<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyStat;
use App\Support\Icons;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StatController extends Controller
{
    public function edit(): View
    {
        abort_unless(request()->user()->canAccessAdmin(), 403);

        return view('admin.stats', ['stats' => CompanyStat::orderBy('sort_order')->get()]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()->canAccessAdmin(), 403);

        $request->validate([
            'stats' => ['required', 'array'],
            'stats.*.label' => ['required', 'string', 'max:80'],
            'stats.*.value' => ['required', 'integer', 'min:0', 'max:100000000'],
            'stats.*.prefix' => ['nullable', 'string', 'max:10'],
            'stats.*.suffix' => ['nullable', 'string', 'max:10'],
            'stats.*.icon' => ['required', 'in:'.implode(',', array_keys(Icons::MAP))],
        ]);

        foreach (CompanyStat::all() as $stat) {
            $row = $request->input("stats.{$stat->id}");

            if (! $row) {
                continue;
            }

            $stat->update([
                'label' => $row['label'],
                'value' => (int) $row['value'],
                'prefix' => $row['prefix'] ?? null,
                'suffix' => $row['suffix'] ?? null,
                'icon' => $row['icon'],
                'is_visible' => ! empty($row['is_visible']),
            ]);
        }

        return back()->with('status', 'Statistics updated.');
    }
}
