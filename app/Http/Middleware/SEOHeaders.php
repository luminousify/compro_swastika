<?php

namespace App\Http\Middleware;

use App\Services\SEOService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SEOHeaders
{
    public function __construct(
        private SEOService $seoService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Add robot headers for staging/development environments
        $robotHeaders = $this->seoService->getRobotHeaders();
        foreach ($robotHeaders as $key => $value) {
            $response->headers->set($key, $value);
        }
        
        // Add security headers
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        return $response;
    }
}