<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Session\TokenMismatchException;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $e)
    {
        // 404 - Model tidak ditemukan
        if ($e instanceof ModelNotFoundException) {
            abort(404, 'Data yang Anda cari tidak ditemukan.');
        }

        // 404 - Route tidak ditemukan
        if ($e instanceof NotFoundHttpException) {
            abort(404, 'Halaman yang Anda akses tidak ditemukan.');
        }

        // 403 - Tidak punya izin
        if ($e instanceof AuthorizationException) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        // 401 - Belum login
        if ($e instanceof AuthenticationException) {
            return redirect()->guest(route('login'))
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        // 405 - Method tidak diizinkan
        if ($e instanceof MethodNotAllowedHttpException) {
            abort(405, 'Metode request tidak diizinkan.');
        }

        // 419 - CSRF / session expired
        if ($e instanceof TokenMismatchException) {
            abort(419, 'Sesi Anda telah berakhir. Silakan refresh halaman.');
        }

        // 503 - Maintenance / under development
        if ($e instanceof HttpException && $e->getStatusCode() === 503) {
            abort(503, $e->getMessage() ?: 'Fitur sedang dalam pengembangan.');
        }

        // 500 - error lain (hanya production)
        if (app()->environment('production')) {
            abort(500, 'Terjadi kesalahan pada sistem. Silakan coba lagi nanti.');
        }

        return parent::render($request, $e);
    }
}
