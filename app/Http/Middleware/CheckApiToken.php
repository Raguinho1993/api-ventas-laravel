<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-API-KEY');

        $llaveMaestra = 'master_2026';
        $llaveLectura = 'read_only_2026';
# descarto las llaves que no coincidan para las diferentes llaves dejando la posibilidad de crear una o vareas llaves mas de acceso
        if(!($token === $llaveMaestra || $token === $llaveLectura)){
            return response()->json([
                'error' => 'Acceso denegado',
                'detalle' => 'El token proporcionado no es válido o ha expirado.'
            ], 401);
        }

        if($token === $llaveMaestra) return $next($request);
    # si el token es igual a la  llaveMaestra  dejamos pasar el objeto tiene acceso a todo

        if(!$request->isMethod('get')) {
        return response()->json([
            'error' => 'Solo lectura',
            'detalle' => 'Solo lEsta llave solo tiene permisos para visualizar datos (GET).',
        ],403);
    # si la request no viene en el metodo http get retorna el eror 403 con el message
        }

        return $next($request);
    # si el metodo es get permite la lectura
    }
}
 