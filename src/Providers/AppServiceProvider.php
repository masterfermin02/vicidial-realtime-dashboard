<?php

namespace Phpdominicana\Lightwave\Providers;

use Phpdominicana\Lightwave\Application;
use Phpdominicana\Lightwave\Database\DatabasePDO;
use Phpdominicana\Lightwave\Gateways\Realtime\RealtimeRepository;
use Phpdominicana\Lightwave\Gateways\VicidialCampaigns\VicidialCampaignFactory;
use Phpdominicana\Lightwave\Gateways\VicidialCampaigns\VicidialCampaignGateway;
use Phpdominicana\Lightwave\Gateways\VicidialCampaigns\VicidialCampaignRepository;
use Phpdominicana\Lightwave\Gateways\VicidialCampaignStats\CampaignStatsFactory;
use Phpdominicana\Lightwave\Gateways\VicidialCampaignStats\VicidialCampaignStatsGateway;
use Phpdominicana\Lightwave\Gateways\VicidialCampaignStats\VicidialCampaignStatsRepository;
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
            'Yes',
            [],
            [],
            [],
            true,
            '',
            $container['psr11Container'],
        );
        $container['RealtimeService'] = fn () => new RealtimeService($app->getContainer()['psr11Container']);
        $container['VicidialCampaignStatsRepository'] = fn () => new VicidialCampaignStatsRepository(
            new VicidialCampaignStatsGateway(
                new DatabasePDO($container['pdo']),
                new CampaignStatsFactory(),
            )
        );
        $container['VicidialCampaignRepository'] = fn () => new VicidialCampaignRepository(
            new VicidialCampaignGateway(
                new DatabasePDO($container['pdo']),
                new VicidialCampaignFactory(),
            )
        );
        $container['DataBaseInterface'] = fn () => new DatabasePDO($container['pdo']);
    }
}
