<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
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

    $isBlocked = DB::table('blocked_ips')
            ->where('ip_address', $request->ip())
            ->exists();

    if ($isBlocked) {
        abort(403, 'Your IP has been permanently blocked due to security violations.');
    }

    $forbiddenWords = ['OR', 'AND', 'XOR','sleep()','sysdate()','%','concat','union','select','insert','update','delete','drop','truncate','exec','declare','--','#','UILIEM','ETahpN','REGEXP_SUBSTRING','CRYPT_KEY','CALL','SLEEP','BENCHMARK','LOAD_FILE','INTO OUTFILE','INFORMATION_SCHEMA','TABLE_NAME','COLUMN_NAME','DATABASE()','USER()','VERSION()','IFNULL','CASE WHEN','GROUP_CONCAT','GROUP BY','HAVING','ORDER BY','LIMIT','INFORMATION_SCHEMA','RLIKE'];

    // Check all input (GET, POST, etc.)
    $input = json_encode($request->all());

    foreach ($forbiddenWords as $word) {
        if (stripos($input, $word) !== false) {
            // Log the attempt or block it
            \Log::warning("Potential SQL Injection attempt blocked", [
                'ip' => $request->ip(),
                'input' => $request->all()
            ]);

            // return response()->json(['error' => 'Security violation.'], 403);
        }
    }

    return $next($request);
}
}
