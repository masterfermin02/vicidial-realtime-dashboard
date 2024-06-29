<?php

namespace Phpdominicana\Lightwave\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        // Perform authentication checks
        if (!$this->isAuthenticated($request)) {
            return new Response('Unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }

    private function isAuthenticated(Request $request): bool
    {
        // Implement your authentication logic here
        return true;
    }
}
