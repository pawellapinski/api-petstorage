<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (ConnectionException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Nie można połączyć się z serwerem API.'], 503);
            }

            return redirect()->back()->with('error', 'Nie można połączyć się z serwerem API. Spróbuj ponownie później.');
        });

        $this->renderable(function (RequestException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Błąd podczas komunikacji z API.',
                    'details' => $e->getMessage()
                ], $e->getCode() ?: 500);
            }

            return redirect()->back()->with('error', 'Wystąpił błąd podczas komunikacji z API. Spróbuj ponownie później.');
        });
    }
}
