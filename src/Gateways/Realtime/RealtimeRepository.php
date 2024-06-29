<?php

namespace Phpdominicana\Lightwave\Gateways\Realtime;

final readonly class RealtimeRepository
{
    public function __construct(
        public string $isInbound,
        public array $miniStatsArray,
        public array $vcAgentsArray,
        public array $vcWaitingList,
    )
    {
    }

    public function inboundOnlyMiniStats(): void
    {

    }

    public function completeMiniStats(): void
    {

    }

    public function callStatus(): void
    {

    }

    public function listCallerIDs(): void
    {

    }

    public function listAgents(): void
    {

    }

    public function getBoxStatus(): array
    {
        return [];
    }

    public function getRealtimeData(): array
    {
        if ($this->isInbound == "ONLY")
            $this->inboundOnlyMiniStats();
        else {
            $this->completeMiniStats();
        }

        $this->callStatus();
        $this->listCallerIDs();
        $this->listAgents();

        $return['stats'] = $this->miniStatsArray;
        $return['agents'] = $this->vcAgentsArray;
        $return['callstatus'] = $this->getBoxStatus();
        $return['waiting'] = $this->vcWaitingList;
        return $return;
    }
}
