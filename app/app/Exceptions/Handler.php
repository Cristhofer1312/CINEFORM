<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Excepciones que no van al log: son respuestas esperadas, no fallas.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        ReglaDeNegocioException::class,
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Regla de negocio: el mensaje es para el usuario. Vuelve al formulario
        // (con lo escrito, menos contraseñas) y lo muestra como error.
        $this->renderable(function (ReglaDeNegocioException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->withInput($request->except($this->dontFlash))->with('error', $e->getMessage());
        });
    }
}
