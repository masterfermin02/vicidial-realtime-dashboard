<?php

namespace Phpdominicana\Lightwave\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MiddlewareRunner
{
    private array $middlewareStack;

    public function __construct(array $middlewareStack)
    {
        $this->middlewareStack = $middlewareStack;
    }

    public function handle(Request $request, callable $controller): Response
    {
        $handler = array_reduce(
            array_map(fn ($middleWare) => new $middleWare,array_reverse($this->middlewareStack)),
            $this->getNextHandler(),
            $controller
        );

        return $handler($request);
    }

    private function getNextHandler(): callable
    {
        return function (callable $next, MiddlewareInterface $middleware) {
            return function (Request $request) use ($next, $middleware) {
                return $middleware->handle($request, $next);
            };
        };
    }
}
