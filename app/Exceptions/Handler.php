<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Redirect;

class Handler extends ExceptionHandler
{

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        // Handle 419 CSRF Token Expired
        if ($exception instanceof TokenMismatchException) {

            // Clear session
            $request->session()->invalidate();

            // Generate new CSRF token
            $request->session()->regenerateToken();

            // Redirect to home page
            return Redirect::to('/');
        }

        return parent::render($request, $exception);
    }
}