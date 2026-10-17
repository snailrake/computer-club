<?php

declare(strict_types=1);

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware()
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->render(function (PDOException $exception): ?Response {
            $connectionName = $exception instanceof QueryException
                ? $exception->getConnectionName()
                : config('database.default');
            $sqliteErrorCodes = ['busy' => 5, 'locked' => 6];

            if ($connectionName !== 'sqlite'
                || ! in_array((int) ($exception->errorInfo[1] ?? 0), $sqliteErrorCodes, true)) {
                return null;
            }

            return response()->view('errors.503', [], Response::HTTP_SERVICE_UNAVAILABLE)->header('Retry-After', '1');
        });
    })->create();
