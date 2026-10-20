<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            try {
                DB::table('audit_logs')->insert([
                    'user_id' => Auth::id(),
                    'action' => 'exception.reported',
                    'description' => get_class($e) . ': ' . substr($e->getMessage(), 0, 1000),
                    'ip_address' => request()->ip(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (Throwable $loggingException) {
                // Never let audit logging hide the original exception.
            }
        });
    }
}
