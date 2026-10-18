<?php

/*
|--------------------------------------------------------------------------
| cPanel front controller (template)
|--------------------------------------------------------------------------
| deploy/cpanel-deploy.sh copies this file into public_html as index.php and
| replaces the placeholder below with the real path of the repository checkout.
| The Laravel app (app, vendor, .env, storage...) stays OUTSIDE the document root.
|
| Deploying by hand instead? Replace __RSQUARE_APP_PATH__ with the absolute path
| of the folder that contains artisan, e.g. /home/cpaneluser/repositories/rsquare
*/

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$appPath = '__RSQUARE_APP_PATH__';

// Maintenance mode
if (file_exists($maintenance = $appPath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $appPath.'/vendor/autoload.php';

/** @var Application $app */
$app = require_once $appPath.'/bootstrap/app.php';

// The document root is where this file lives, not <app>/public.
$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());
