<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'login',
            'register',
        ]);

        $middleware->web(append: [
            \Fahlisaputra\Minify\Middleware\MinifyHtml::class,
            \Fahlisaputra\Minify\Middleware\MinifyCss::class,
            \Fahlisaputra\Minify\Middleware\MinifyJavascript::class,
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'role' => \App\Http\Middleware\CheckRoleMiddleware::class,
            'penawaran.accepted' => \App\Http\Middleware\EnsurePenawaranAcceptedMiddleware::class,
            'under.dev' => \App\Http\Middleware\UnderDevelopmentMiddleware::class,
            'sync.roles' => \App\Http\Middleware\SyncUserRolesMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
