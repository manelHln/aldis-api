<?php

use App\Exceptions\AldisModelNotFoundException;
use App\Http\Middleware\EnsurePhoneNumberIsVerified;
use \App\Http\Middleware\HasPermission;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
        $middleware->statefulApi();
        $middleware->alias([
            'phone_verified' => EnsurePhoneNumberIsVerified::class,
            // 'has_permission' => HasPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
        $exceptions->render(function(ValidationException $e, Request $request){
            if($request->is('api/*')){
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
        });

        $exceptions->render(function(AuthenticationException $e, Request $request){
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthenticated. Please login again.',
                    'errors' => [],
                ], 401);
            }
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => $e->getMessage() or 'You are not authorized to access this resource.',
                    'errors' => [],
                ], 403);  // Forbidden
            }
        });

        // $exceptions->render(function (UnauthorizedHttpException $e, Request $request) {
        //     if ($request->is('api/*')) {
        //         return response()->json([
        //             'message' => 'You are not authorized to access this resource.',
        //             'errors' => [],
        //         ], 401);
        //     }
        // });

        $exceptions->render(function(AldisModelNotFoundException $e, Request $request){
            if($request->is('api/*')){
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors' => []
                ], 404);
            }
        });

        // $exceptions->render(function(MethodNotAllowedHttpException $e, Request $request){
        //     if($request->is('api/*')){
        //         return response()->json([
        //             'message' => $e->getMessage(),
        //             'errors' => []
        //         ], 405);
        //     }
        // });

        // $exceptions->render(function(AccessDeniedHttpException $e, Request $request){
        //     if($request->is('api/*')){
        //         return response()->json([
        //             'message' => $e->getMessage(),
        //             'errors' => []
        //         ], $e->getStatusCode());
        //     }
        // });

        $exceptions->render(function(HttpException $e, Request $request){
            if($request->is('api/*')){
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors' => []
                ], $e->getStatusCode());
            }
        });

        $exceptions->render(function(Throwable $e, Request $request){
            if($request->is('api/*')){
                Log::error($e->getMessage(), [
                    'trace' => $e->getTrace()
                ]);

                if (config('app.debug')) {
                    return response()->json([
                        'message' => $e->getMessage(),
                        'errors' => [],
                        "type" => get_class($e)
                    ], 500);
                }

                return response()->json([
                    'message' => 'Internal server error',
                    'errors' => []
                ], 500);
            }
        });

        $exceptions->shouldRenderJsonWhen(function(Request $request, Throwable $e){
            if ($request->is('api/*')){
                return true;
            }
        });
    })->create();
