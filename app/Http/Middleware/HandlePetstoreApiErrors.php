<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class HandlePetstoreApiErrors
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (ConnectionException $e) {
            Log::error('API Connection Error', [
                'exception' => $e->getMessage(),
                'url' => $request->url(),
                'method' => $request->method(),
                'trace' => $e->getTraceAsString(),
                'user_agent' => $request->userAgent()
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Nie można połączyć się z serwerem API.'], 503);
            }

            return redirect()->back()->with('error', 'Nie można połączyć się z serwerem API. Spróbuj ponownie później.');
        } catch (RequestException $e) {
            $responseData = [];

            if (isset($e->response)) {
                $responseData = [
                    'status' => $e->response->status(),
                    'body' => $e->response->body(),
                    'headers' => $e->response->headers()
                ];
            }

            Log::error('API Request Error', [
                'exception' => $e->getMessage(),
                'url' => $request->url(),
                'method' => $request->method(),
                'request_data' => $request->all(),
                'response' => $responseData,
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Błąd podczas komunikacji z API.',
                    'details' => $e->getMessage()
                ], $e->getCode() ?: 500);
            }

            return redirect()->back()->with('error', 'Wystąpił błąd podczas komunikacji z API. Spróbuj ponownie później.');
        } catch (\Exception $e) {
            Log::error('Generic Error', [
                'exception' => $e->getMessage(),
                'url' => $request->url(),
                'method' => $request->method(),
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
                'exception_class' => get_class($e)
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Wystąpił błąd.'], 500);
            }

            return redirect()->back()->with('error', 'Wystąpił nieoczekiwany błąd. Spróbuj ponownie później.');
        }
    }
}
