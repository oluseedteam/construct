<?php

namespace App\Providers;

use App\Models\ContactMessage;
use App\Models\GalleryImage;
use App\Models\Highlight;
use App\Models\JobApplication;
use App\Models\JobOpening;
use App\Models\Page;
use App\Models\Project;
use App\Models\QuoteRequest;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\User;
use App\Policies\ContentPolicy;
use App\Support\Site;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(Site::class);
    }

    public function boot(): void
    {
        if ($this->app->isProduction() && str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // One policy covers every admin-managed model: staff can create/edit, only admins can delete.
        foreach ([Project::class, Service::class, GalleryImage::class, Testimonial::class, QuoteRequest::class, ContactMessage::class, JobOpening::class, JobApplication::class, Highlight::class, Page::class] as $model) {
            Gate::policy($model, ContentPolicy::class);
        }

        Gate::define('manage-settings', fn (User $user) => $user->isAdmin() && $user->is_active);

        // Public forms: a short burst limit plus an hourly cap per IP address.
        RateLimiter::for('forms', fn (Request $request) => [
            Limit::perMinute(5)->by($request->ip()),
            Limit::perHour(30)->by($request->ip()),
        ]);

        RateLimiter::for('admin-login', fn (Request $request) => [
            Limit::perMinute(10)->by($request->ip()),
        ]);
    }
}
