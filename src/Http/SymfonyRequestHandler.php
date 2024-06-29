<?php

namespace Phpdominicana\Lightwave\Http;

use Phpdominicana\Lightwave\Middleware\MiddlewareRunner;
use Phpdominicana\Lightwave\RequestHandlerInterface;
use Phpdominicana\Lightwave\ResponseHandlerInterface;
use Pimple\Psr11\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

class SymfonyRequestHandler implements RequestHandlerInterface
{
    protected RouteCollection $routeCollection;

    public function __construct(
        protected Container $container,
    ) {}

    public function setRouteCollection(RouteCollection $routeCollection): void
    {
        $this->routeCollection = $routeCollection;
    }

    #[\Override] public function handleRequest(): ResponseHandlerInterface
    {
        // Create a context and matcher
        $context = new RequestContext();
        $request = Request::createFromGlobals();
        $context->fromRequest($request);
        $matcher = new UrlMatcher($this->routeCollection, $context);

        // Match the current request to a route
        try {
            $parameters = $matcher->match($context->getPathInfo());
            $controller = $parameters['_controller'];
            $middleware = $parameters['_middleware'] ?? [];
            unset($parameters['_controller'], $parameters['_route'], $parameters['_middleware']);

            $middlewareStack = [];
            foreach ($middleware as $mw) {
                if (is_string($mw) && isset($this->middlewareGroups[$mw])) {
                    $middlewareStack = array_merge($middlewareStack, $this->middlewareGroups[$mw]);
                } else {
                    $middlewareStack[] = $mw;
                }
            }

            $runner = new MiddlewareRunner($middlewareStack);
            $response = $runner->handle($request, function (Request $request) use ($controller, $parameters) {
                // Create an instance of the controller
                $controllerInstance = new $controller[0]($this->container);

                // Get the method parameters
                $reflection = new \ReflectionClass($controllerInstance);
                $method = $reflection->getMethod($controller[1]);
                $methodParameters = $method->getParameters();

                // Prepare the parameters for the method call
                $callParameters = [];
                foreach ($methodParameters as $param) {
                    $paramName = $param->getName();
                    if (isset($parameters[$paramName])) {
                        $callParameters[] = $parameters[$paramName];
                    } else if ($param instanceof Request) {
                        $callParameters[] = $request;
                    } else if ($this->container->has($paramName)) {
                        $callParameters[] = $this->container->get($paramName);
                    } else if ($param->isDefaultValueAvailable()) {
                        $callParameters[] = $param->getDefaultValue();
                    } else {
                        throw new \Exception("Missing parameter: $paramName");
                    }
                }

                // Call the method on the controller instance
                return $method->invokeArgs($controllerInstance, $callParameters);
            });

        } catch (ResourceNotFoundException $e) {
            $response = new Response('Not Found', 404);
        } catch (\Exception $e) {
            $response = new Response('An error occurred: ' . $e->getMessage(), 500);
        }

        return new SymfonyResponseHandler($response);
    }
}
