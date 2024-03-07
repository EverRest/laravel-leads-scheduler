<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class JsonExceptionHandler extends ExceptionHandler
{
    /**
     * @param Exception|Throwable $e
     *
     * @return void
     * @throws Throwable
     */
    public function report(Exception|Throwable $e): void
    {
        parent::report($e);
    }

    /**
     * @param $request
     * @param Exception|Throwable $e
     *
     * @return Response|JsonResponse|RedirectResponse|SymfonyResponse
     * @throws Throwable
     */
    public function render($request, Exception|Throwable $e): Response|JsonResponse|RedirectResponse|SymfonyResponse
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->handleApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * @param $request
     * @param AuthenticationException $exception
     *
     * @return JsonResponse
     */
    protected function handleApiException($request, Throwable $exception): JsonResponse
    {
        $statusCode = $this->getStatusCode($exception);

        return response()->json([
            'error' => [
                'code' => $statusCode,
                'message' => $exception->getMessage(),
            ],
        ], $statusCode);
    }

    /**
     * @param Throwable $exception
     *
     * @return int
     */
    protected function getStatusCode(Throwable $exception): int
    {
        if ($exception instanceof HttpException) {
            return $exception->getStatusCode();
        } elseif ($exception instanceof ModelNotFoundException) {
            return 404;
        } elseif ($exception instanceof ValidationException) {
            return 422;
        }
        return 500;
    }
}
