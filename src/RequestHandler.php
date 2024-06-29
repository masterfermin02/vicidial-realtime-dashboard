<?php

namespace Phpdominicana\Lightwave;

class RequestHandler implements RequestHandlerInterface
{
    public function __construct(
        protected Request $request,
        protected Dispatcher $dispatcher,
        protected ContainerInterface $container,
    ) {
    }

    public function handleRequest() : ResponseHandlerInterface
    {
        try {
            $routeInfo = $this->dispatcher->dispatch(
                $this->request->method->name,
                $this->request->url->path,
            );

            switch ($routeInfo[0]) {
                case Dispatcher::NOT_FOUND:
                    $callable = $this->container->get(Error\RouteNotFound::class);
                    $arguments = [];
                    break;

                case Dispatcher::METHOD_NOT_ALLOWED:
                    $callable = $this->container->get(Error\MethodNotAllowed::class);
                    $arguments = [$routeInfo[1]];
                    break;

                default:
                    $callable = $this->container->get($routeInfo[1]);
                    $arguments = $routeInfo[2];
                    break;
            }

            $response = $callable(...$arguments);
        } catch (Throwable $e) {
            $response = new Response();
            $response->setCode(500);
            $response->setHeader('content-type', 'text-plain');
            $response->setBody(get_class($e) . PHP_EOL . $e->getMessage() . PHP_EOL);
        }

        return new SapienResponseHandler($response);
    }
}
