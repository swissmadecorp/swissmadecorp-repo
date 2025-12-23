<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockIpMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Define the array of blocked IP addresses
        $blockedIps = ['107.164.78.179','208.100.0.117','108.84.44.200','12.203.57.30'];

        // Get the client's IP address
        $clientIp = $request->ip();

        // Check if the client's IP matches any blocked IP
        if (in_array($clientIp, $blockedIps)) {
            // Optionally, you can log the blocked attempt or perform other actions

            // Return a response indicating that the request is blocked
            //return response('Unauthorized', 401);
            abort('401');
        }

        // Continue with the request if the IP is not blocked
        return $next($request);
    }
}
