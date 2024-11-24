<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (ModelNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                if ($e->getModel() === \App\Models\Notification::class) {
                    return response()->json([
                        'status' => 'failure',
                        'message' => 'Notification not found.',
                    ], 404);
                }
            }
        });
    }
}
