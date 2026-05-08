<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        $middleware->alias([
            'admin.auth' => \App\Http\Middleware\AdminAuth::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return null;
            }

            return Inertia::render('errors/404')
                ->toResponse($request)
                ->setStatusCode(404);
        });

        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if (config('app.debug') || $request->expectsJson()) {
                return null;
            }

            $status = $e->getStatusCode();
            if (! in_array($status, [500, 503], true)) {
                return null;
            }

            return Inertia::render('errors/500', ['code' => $status])
                ->toResponse($request)
                ->setStatusCode($status);
        });
    })->create();
