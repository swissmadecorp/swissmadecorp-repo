<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DetectSqlInjection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $forbiddenWords = ['OR', 'AND', 'XOR','sleep()','sysdate()','%','concat','union','select','insert','update','delete','drop','truncate','exec','declare','--','#','UILIEM','ETahpN','REGEXP_SUBSTRING','CRYPT_KEY'];

        // Check all input (GET, POST, etc.)
        $input = json_encode($request->all());

        foreach ($forbiddenWords as $word) {
            if (stripos($input, $word) !== false) {
                // Log the attempt or block it
                Log::warning("Potential SQL Injection attempt blocked", [
                    'ip' => $request->ip(),
                    'input' => $request->all()
                ]);

                return response()->json(['error' => 'Security violation.'], 403);
            }
        }

        return $next($request);
    }
}
