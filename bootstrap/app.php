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

        // Minify middleware - disabled karena package issue
        // $middleware->web(append: [
        //     \Fahlisaputra\Minify\Middleware\MinifyHtml::class,
        //     \Fahlisaputra\Minify\Middleware\MinifyCss::class,
        //     \Fahlisaputra\Minify\Middleware\MinifyJavascript::class,
        // ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'role' => \App\Http\Middleware\CheckRoleMiddleware::class,
            'penawaran.accepted' => \App\Http\Middleware\EnsurePenawaranAcceptedMiddleware::class,
            'al.first.opener' => \App\Http\Middleware\CheckAlFirstOpenerMiddleware::class,
            'under.dev' => \App\Http\Middleware\UnderDevelopmentMiddleware::class,
            'sync.roles' => \App\Http\Middleware\SyncUserRolesMiddleware::class,
            'verify.payment.password' => \App\Http\Middleware\VerifyPaymentPassword::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
