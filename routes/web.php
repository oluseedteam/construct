<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public website
|--------------------------------------------------------------------------
*/
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/about', [PageController::class, 'about'])->middleware('page:about')->name('about');
Route::get('/why-choose-us', [PageController::class, 'whyUs'])->middleware('page:why-us')->name('why-us');
Route::get('/gallery', [PageController::class, 'gallery'])->middleware('page:gallery')->name('gallery');

Route::middleware('page:services')->group(function () {
    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::get('/services/{service:slug}', [ServiceController::class, 'show'])->name('services.show');
});

Route::middleware('page:projects')->group(function () {
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/{project:slug}', [ProjectController::class, 'show'])->name('projects.show');
});

Route::middleware('page:careers')->group(function () {
    Route::get('/careers', [CareerController::class, 'index'])->name('careers.index');
    Route::get('/careers/apply', [CareerController::class, 'apply'])->name('careers.apply');
    Route::post('/careers/apply', [CareerController::class, 'store'])->middleware('throttle:forms')->name('careers.store');
    Route::get('/careers/{job:slug}', [CareerController::class, 'show'])->name('careers.show');
});

Route::middleware('page:contact')->group(function () {
    Route::get('/contact', [ContactController::class, 'show'])->name('contact');
    Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:forms')->name('contact.store');
});

Route::middleware('page:quote')->group(function () {
    Route::get('/request-a-quote', [QuoteController::class, 'show'])->name('quote');
    Route::post('/request-a-quote', [QuoteController::class, 'store'])->middleware('throttle:forms')->name('quote.store');
});

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

/*
|--------------------------------------------------------------------------
| Admin dashboard (/admin)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [Admin\AuthController::class, 'create'])->name('login');
        Route::post('login', [Admin\AuthController::class, 'store'])->middleware('throttle:admin-login')->name('login.store');
    });

    Route::middleware(['auth', 'staff'])->group(function () {
        Route::post('logout', [Admin\AuthController::class, 'destroy'])->name('logout');
        Route::get('/', Admin\DashboardController::class)->name('dashboard');

        Route::resource('projects', Admin\ProjectController::class)->except('show');
        Route::delete('projects/{project}/images/{image}', [Admin\ProjectController::class, 'destroyImage'])->name('projects.images.destroy');

        Route::resource('services', Admin\ServiceController::class)->except('show');
        Route::patch('services/{service}/toggle', [Admin\ServiceController::class, 'toggle'])->name('services.toggle');

        Route::get('quotes', [Admin\QuoteController::class, 'index'])->name('quotes.index');
        Route::get('quotes/{quote}', [Admin\QuoteController::class, 'show'])->name('quotes.show');
        Route::patch('quotes/{quote}', [Admin\QuoteController::class, 'update'])->name('quotes.update');
        Route::get('quotes/{quote}/document', [Admin\QuoteController::class, 'document'])->name('quotes.document');
        Route::post('quotes/{quote}/respond', [Admin\QuoteController::class, 'respond'])->name('quotes.respond');
        Route::delete('quotes/{quote}', [Admin\QuoteController::class, 'destroy'])->name('quotes.destroy');

        Route::get('messages', [Admin\MessageController::class, 'index'])->name('messages.index');
        Route::get('messages/{message}', [Admin\MessageController::class, 'show'])->name('messages.show');
        Route::patch('messages/{message}', [Admin\MessageController::class, 'update'])->name('messages.update');
        Route::delete('messages/{message}', [Admin\MessageController::class, 'destroy'])->name('messages.destroy');

        Route::get('gallery', [Admin\GalleryController::class, 'index'])->name('gallery.index');
        Route::post('gallery', [Admin\GalleryController::class, 'store'])->name('gallery.store');
        Route::patch('gallery/{image}', [Admin\GalleryController::class, 'update'])->name('gallery.update');
        Route::delete('gallery/{image}', [Admin\GalleryController::class, 'destroy'])->name('gallery.destroy');

        Route::resource('jobs', Admin\JobController::class)->except('show')->parameters(['jobs' => 'job']);
        Route::patch('jobs/{job}/toggle', [Admin\JobController::class, 'toggle'])->name('jobs.toggle');

        Route::get('applications', [Admin\ApplicationController::class, 'index'])->name('applications.index');
        Route::get('applications/{application}', [Admin\ApplicationController::class, 'show'])->name('applications.show');
        Route::patch('applications/{application}', [Admin\ApplicationController::class, 'update'])->name('applications.update');
        Route::get('applications/{application}/resume', [Admin\ApplicationController::class, 'resume'])->name('applications.resume');
        Route::delete('applications/{application}', [Admin\ApplicationController::class, 'destroy'])->name('applications.destroy');

        Route::resource('highlights', Admin\HighlightController::class)->except('show');

        Route::resource('pages', Admin\PageController::class)->except('show');
        Route::patch('pages/{page}/toggle', [Admin\PageController::class, 'toggle'])->name('pages.toggle');

        Route::resource('users', Admin\UserController::class)->except('show');
        Route::get('profile', [Admin\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [Admin\ProfileController::class, 'update'])->name('profile.update');

        Route::resource('testimonials', Admin\TestimonialController::class)->except('show');
        Route::get('stats', [Admin\StatController::class, 'edit'])->name('stats.edit');
        Route::put('stats', [Admin\StatController::class, 'update'])->name('stats.update');

        Route::get('content', [Admin\ContentController::class, 'edit'])->name('content.edit');
        Route::put('content', [Admin\ContentController::class, 'update'])->name('content.update');

        Route::get('settings', [Admin\SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [Admin\SettingsController::class, 'update'])->name('settings.update');
    });
});

/*
|--------------------------------------------------------------------------
| Custom pages created in Admin > Pages (must stay last: /{slug})
|--------------------------------------------------------------------------
*/
// any() so that POSTing to an unknown URL is a plain 404 rather than a 405 that reveals the catch-all.
Route::any('/{slug}', [PageController::class, 'show'])->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')->name('pages.show');
