<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $renderUnauthorized = function (Request $request) {
            if (! $request->expectsJson() && auth()->check() && ! auth()->user()->isAdmin()) {
                $fallbackUrl = route('dashboard');
                $referer = $request->headers->get('referer');
                $targetUrl = $fallbackUrl;

                if (is_string($referer) && $referer !== '') {
                    $refererHost = parse_url($referer, PHP_URL_HOST);

                    if ($refererHost === $request->getHost() && $referer !== $request->fullUrl()) {
                        $targetUrl = $referer;
                    }
                }

                return redirect()->to($targetUrl)->with('access_denied_modal', [
                    'title' => 'Acceso restringido',
                    'message' => 'No tienes acceso para entrar o modificar esta seccion sin autorizacion del administrador.',
                    'detail' => 'Si necesitas este permiso, solicitalo al administrador del sistema.',
                ]);
            }
        };

        $exceptions->render(function (AuthorizationException $e, Request $request) use ($renderUnauthorized) {
            return $renderUnauthorized($request);
        });

        $exceptions->render(function (HttpExceptionInterface $e, Request $request) use ($renderUnauthorized) {
            if ($e->getStatusCode() === 403) {
                return $renderUnauthorized($request);
            }
        });
    })->create();
