<?php

namespace Phpdominicana\Lightwave;

use ReflectionClass;
use Pimple\Container;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Application
{
    protected array $providers = [];
    protected Container $container;

    protected Config $config;

    protected RouteCollection $routes;

    public function __construct(
        Container $injector,
        Config $config
    )
    {
        $this->providers = $config->get('app.providers');
        $this->container = $injector;
        $this->config = $config;
        $this->routes = new RouteCollection();
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function get(string $name, Route $route): void
    {
        $route->setMethods(['GET', 'HEAD']);
        $this->routes->add($name, $route);
    }

    public function post(string $name, Route $route)
    {
        $route->setMethods(['POST', 'HEAD']);
        $this->routes->add($name, $route);
    }

    public function put(string $name, Route $route)
    {
        $route->setMethods(['PUT', 'HEAD']);
        $this->routes->add($name, $route);
    }

    public function delete(string $name, Route $route)
    {
        $route->setMethods(['DELETE', 'HEAD']);
        $this->routes->add($name, $route);
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @throws \ReflectionException
     */
    public function run(): void
    {
        $this->loadProviders();

        $requestHandler =  $this->container['RequestHandlerInterface'];
        $requestHandler->setRouteCollection($this->routes);
        $requestHandler
            ->handleRequest()
            ->handleResponse();
    }

    /**
     * @throws \ReflectionException
     */
    protected function loadProviders(): void
    {
        foreach ($this->providers as $provider) {
            if (is_callable($provider)) {
                $provider = $provider();
                $provider->register($this);
            } else {
                $reflection = new ReflectionClass($provider);
                $reflection->newInstance()->register($this);
            }
        }
    }
}
