<?php

namespace Phpdominicana\Lightwave\Providers;

use Phpdominicana\Lightwave\Application;
use Phpdominicana\Lightwave\Gateways\Realtime\RealtimeRepository;
use Phpdominicana\Lightwave\Gateways\VicidiaLiveAgent\VicidialLiveAgentGateway;
use Phpdominicana\Lightwave\Services\RealtimeService;
use Phpdominicana\Lightwave\Services\VicidialLiveAgentService;
use Pimple\Psr11\Container;

class AppServiceProvider implements ProviderInterface
{
    public function register(Application $app): void
    {
        $container = $app->getContainer();
        $container['psr11Container'] = fn () => new Container($container);
        $container['VicidialLiveAgentService']  = fn () => new VicidialLiveAgentService($container['psr11Container']);
        $container['VicidialLiveAgentGateway']  = fn () => new VicidialLiveAgentGateway($container['psr11Container']);
        $container['RealtimeRepository'] = fn () => new RealtimeRepository(
            'ONLY',
            [],
            [],
            [],
        );
        $container['RealtimeService'] = fn () => new RealtimeService($app->getContainer()['psr11Container']);
    }
}
