<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
    $middleware->validateCsrfTokens(except: [
        'tasks',
        'tasks/*',
        'projects',
        'projects/*',
        'comments/',
        'comments/*',
    ]);
})
    ->withExceptions(function (Exceptions $exceptions) {
        // 1. ถ้า URL ขึ้นต้นด้วย api/ ให้ตอบกลับเป็น JSON เสมอ (แม้ลืมใส่ Header Accept ก็ตาม)
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*')
        );
        // 2. ปรับข้อความ 404 Not Found ให้สะอาด เป็นมาตรฐานเดียวกัน
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Not found'], 404);
            }
        });
    })->create();
