<?php

namespace App\Http\Middleware;

use App\Support\Pages;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Returns 404 for a built-in page (and its sub-pages) that an admin has unpublished. */
class EnsurePagePublished
{
    public function handle(Request $request, Closure $next, string $key): Response
    {
        abort_unless(Pages::isPublished($key), 404);

        return $next($request);
    }
}
