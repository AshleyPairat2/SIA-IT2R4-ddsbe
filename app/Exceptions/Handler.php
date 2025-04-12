<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;
use Illuminate\Http\JsonResponse;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception): JsonResponse
    {
        // Handle Validation Errors (422)
        if ($exception instanceof ValidationException) {
            return response()->json([
                'status' => 422,
                'error' => 'Validation Error',
                'message' => $exception->validator->errors(),
            ], 422);
        }

        // Handle Model Not Found (404)
        if ($exception instanceof ModelNotFoundException) {
            return response()->json([
                'status' => 404,
                'error' => 'Not Found',
                'message' => 'The requested resource could not be found.',
            ], 404);
        }

        // Handle Authorization Errors (403)
        if ($exception instanceof AuthorizationException) {
            return response()->json([
                'status' => 403,
                'error' => 'Forbidden',
                'message' => 'You do not have permission to access this resource.',
            ], 403);
        }

        if ($exception instanceof HttpException) {
            $status = $exception->getStatusCode();
        
            if ($status === 401) {
                return response()->json([
                    'status' => 401,
                    'error' => 'Unauthorized',
                    'message' => $exception->getMessage() ?: 'Token not provided or invalid.'
                ], 401);
            }
        
            return response()->json([
                'status' => $status,
                'error' => 'HTTP Error',
                'message' => $exception->getMessage() ?: 'An error occurred.',
            ], $status);
        }        

        // Handle Unexpected Errors (500)
        return response()->json([
            'status' => 500,
            'error' => 'Server Error',
            'message' => env('APP_DEBUG') ? $exception->getMessage() : 'An unexpected error occurred.',
        ], 500);
    }
}
