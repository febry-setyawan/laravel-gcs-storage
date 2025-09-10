<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request as HttpRequest;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        ]);
    })
    ->withExceptions(function () {
        //
    })->create();

// When running in CLI (artisan/composer scripts) some providers expect a
// bound 'request' instance. Bind a minimal console request early so
// UrlGenerator and other services don't receive null. Avoid calling
// other services (like config) which may not be available yet.
if (php_sapi_name() === 'cli' && ! $app->bound('request')) {
    $uri = $_ENV['APP_URL'] ?? $_SERVER['APP_URL'] ?? 'http://localhost';
    $components = parse_url($uri);

    $server = $_SERVER;
    if (! empty($components['path'])) {
        $server = array_merge($server, [
            'SCRIPT_FILENAME' => $components['path'],
            'SCRIPT_NAME' => $components['path'],
        ]);
    }

    $app->instance('request', HttpRequest::create($uri, 'GET', [], [], [], $server));
}

return $app;
