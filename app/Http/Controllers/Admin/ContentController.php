<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageContent;
use App\Support\MediaStorage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Edits every piece of website copy and the main images, driven by config/rsquare.php `content_schema`. */
class ContentController extends Controller
{
    /** "home.hero_title" => "home__hero_title" (dots are not safe in HTML field names). */
    public static function field(string $key): string
    {
        return str_replace('.', '__', $key);
    }

    public function edit(): View
    {
        abort_unless(request()->user()->canAccessAdmin(), 403);

        return view('admin.content', ['tabs' => config('rsquare.content_schema')]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()->canAccessAdmin(), 403);

        $rules = [];
        $texts = [];
        $images = [];

        foreach (config('rsquare.content_schema') as $fields) {
            foreach ($fields as $key => [, $type, $max]) {
                $field = self::field($key);

                if ($type === 'image') {
                    $rules[$field] = ['nullable', ...MediaStorage::IMAGE_RULES];
                    $images[$key] = $field;
                } else {
                    $rules[$field] = ['nullable', 'string', "max:$max"];
                    $texts[$key] = $field;
                }
            }
        }

        $request->validate($rules);

        foreach ($texts as $key => $field) {
            // Only touch fields that were submitted, so a partial form can't wipe other content.
            if (! $request->exists($field)) {
                continue;
            }

            $value = trim((string) $request->input($field));

            // A blank field falls back to the built-in default text.
            if ($value === '') {
                PageContent::where('key', $key)->delete();
            } else {
                PageContent::updateOrCreate(['key' => $key], ['value' => $value]);
            }
        }

        foreach ($images as $key => $field) {
            $current = PageContent::where('key', $key)->value('value');

            if ($request->hasFile($field)) {
                MediaStorage::delete($current);
                PageContent::updateOrCreate(['key' => $key], ['value' => MediaStorage::storeImage($request->file($field), 'content')]);
            } elseif ($request->boolean('reset__'.$field)) {
                MediaStorage::delete($current);
                PageContent::where('key', $key)->delete();
            }
        }

        // Blank fields are removed with bulk deletes, which don't fire model events.
        PageContent::flushCache();

        return back()->with('status', 'Website content updated.');
    }
}
