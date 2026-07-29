<?php
/*
|--------------------------------------------------------------------------
| TAMBAHKAN INI ke bootstrap/app.php project Laravel Anda (Laravel 11)
|--------------------------------------------------------------------------
|
| Cari bagian ->withMiddleware(function (Middleware $middleware) { ... })
| lalu tambahkan baris alias di dalamnya seperti contoh berikut:
|
*/

use App\Http\Middleware\CheckRole;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => CheckRole::class,
        ]);
    })
    ->withExceptions(function ($exceptions) {
        //
    })->create();

/*
|--------------------------------------------------------------------------
| Jika menggunakan Laravel versi < 11 (struktur app/Http/Kernel.php lama):
|--------------------------------------------------------------------------
| Tambahkan baris berikut ke $middlewareAliases (atau $routeMiddleware)
| di dalam app/Http/Kernel.php:
|
|   'role' => \App\Http\Middleware\CheckRole::class,
|
*/
