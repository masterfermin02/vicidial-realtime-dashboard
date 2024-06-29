<?php

namespace Phpdominicana\Lightwave\Services;

use Pimple\Psr11\Container;

class VicidialLiveAgentService
{
    public function __construct(
        protected Container $container
    )
    {
    }
    public function getAgents(): array
    {
        $vicidialLiveAgentWay = $this->container->get('VicidialLiveAgentGateway');
        return $vicidialLiveAgentWay->getAgents();
    }
}
