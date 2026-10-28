<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Support\MediaStorage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    private const TEXT_KEYS = [
        'company_name', 'tagline', 'email', 'phone', 'whatsapp', 'address',
        'facebook', 'instagram', 'linkedin', 'x_twitter', 'youtube', 'tiktok',
        'seo_title', 'seo_description',
    ];

    public function edit(): View
    {
        $this->authorize('manage-settings');

        return view('admin.settings');
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorize('manage-settings');

        $request->validate([
            'company_name' => ['required', 'string', 'max:120'],
            'tagline' => ['nullable', 'string', 'max:80'],
            'email' => ['nullable', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'whatsapp' => ['nullable', 'string', 'max:40', 'regex:/^[0-9+\-\s()]*$/'],
            'address' => ['nullable', 'string', 'max:300'],
            'facebook' => ['nullable', 'url:http,https', 'max:255'],
            'instagram' => ['nullable', 'url:http,https', 'max:255'],
            'linkedin' => ['nullable', 'url:http,https', 'max:255'],
            'x_twitter' => ['nullable', 'url:http,https', 'max:255'],
            'youtube' => ['nullable', 'url:http,https', 'max:255'],
            'tiktok' => ['nullable', 'url:http,https', 'max:255'],
            'seo_title' => ['nullable', 'string', 'max:70'],
            'seo_description' => ['nullable', 'string', 'max:200'],
            // SVG is intentionally not accepted: it can carry scripts.
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'seo_image' => ['nullable', ...MediaStorage::IMAGE_RULES],
            // Square site icon for browser tabs and phone home screens.
            'favicon' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024', 'dimensions:min_width=64,min_height=64'],
        ]);

        foreach (self::TEXT_KEYS as $key) {
            $value = trim((string) $request->input($key));
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value === '' ? null : $value]);
        }

        foreach (['logo', 'favicon', 'seo_image'] as $key) {
            $current = SiteSetting::where('key', $key)->value('value');

            if ($request->hasFile($key)) {
                MediaStorage::delete($current);
                SiteSetting::updateOrCreate(['key' => $key], ['value' => MediaStorage::storeImage($request->file($key), 'branding')]);
            } elseif ($request->boolean("remove_$key")) {
                MediaStorage::delete($current);
                SiteSetting::updateOrCreate(['key' => $key], ['value' => null]);
            }
        }

        return back()->with('status', 'Settings saved.');
    }
}
