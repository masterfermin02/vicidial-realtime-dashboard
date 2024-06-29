<?php

namespace Phpdominicana\Lightwave\Services;

use Pimple\Psr11\Container;

class RealtimeService
{
    public function __construct(
        protected Container $container
    )
    {
    }

    public function getRealtimeData(): array
    {
        $realtimeRepository = $this->container->get('RealtimeRepository');
        return $realtimeRepository->getRealtimeData();
    }
}
