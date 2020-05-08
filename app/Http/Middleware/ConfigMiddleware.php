<?php
namespace App\Http\Middleware;
use Closure;

class ConfigMiddleware
{
  /**
  * Handle an incoming request.
  * Vilken mrbs-app/databas?
  *
  * @param \Illuminate\Http\Request $request
  * @param \Closure $next
  * @return mixed
  */
  public function handle($request, Closure $next)
  {
    if($request->has('database')){
        config(['database.connections.mysql.database' => $request->input('database')]);
    } else {
        return response()->json([
            'error' => 'database not provided.'
        ], 401);
    }
    if($request->has('appname')){
        config(['app.name' => $request->input('appname')]);
    } else {
        return response()->json([
            'error' => 'appname not provided.'
        ], 401);
    }
    return $next($request);
  }
}