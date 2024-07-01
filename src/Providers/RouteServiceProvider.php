<?php

namespace Phpdominicana\Lightwave\Providers;

use Phpdominicana\Lightwave\Application;
use Phpdominicana\Lightwave\Controllers\DashboardController;
use Phpdominicana\Lightwave\Controllers\HelloController;
use Phpdominicana\Lightwave\Controllers\HomeController;
use Phpdominicana\Lightwave\Controllers\LoginController;
use Phpdominicana\Lightwave\Middleware\AuthenticationMiddleware;
use Pimple\Psr11\Container as Psr11Container;
use Symfony\Component\Routing\Route;

class RouteServiceProvider implements ProviderInterface
{

    #[\Override] public function register(Application $app): void
    {
        $app->get('hello', new Route('/hello/{name}', ['_controller' => [HelloController::class, 'index']]));
        $app->get('home',
            new Route(
                '/',
                [
                    '_controller' => [DashboardController::class, 'index'],
                    'container' => new Psr11Container($app->getContainer()),
                    '_middleware' => [
                        AuthenticationMiddleware::class,
                    ]
                ]
            )
        );
        $app->get('dashboard_sse',
            new Route(
                '/dashboard/sse',
                [
                    '_controller' => [DashboardController::class, 'sse'],
                    'container' => new Psr11Container($app->getContainer()),
                    '_middleware' => [
                        AuthenticationMiddleware::class,
                    ]
                ]
            )
        );
        $app->get('login',
            new Route(
                '/login',
                [
                    '_controller' => [LoginController::class, 'index'],
                    'container' => new Psr11Container($app->getContainer()),
                ]
            )
        );
        $app->post('login.attention',
            new Route(
                '/login',
                [
                    '_controller' => [LoginController::class, 'login'],
                    'container' => new Psr11Container($app->getContainer())
                ]
            )
        );
    }
}
