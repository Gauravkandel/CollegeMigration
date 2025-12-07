<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Increase PHP execution time
|--------------------------------------------------------------------------
|
| This will allow long-running scripts (imports, exports, big migrations)
| to run without hitting the "Maximum execution time exceeded" error.
|
*/
ini_set('max_execution_time', 10000); // 300 seconds = 5 minutes
// OR use unlimited execution time
// set_time_limit(0);

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
