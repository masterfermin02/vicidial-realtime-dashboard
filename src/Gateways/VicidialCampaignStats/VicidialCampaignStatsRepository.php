<?php

namespace Phpdominicana\Lightwave\Gateways\VicidialCampaignStats;

class VicidialCampaignStatsRepository
{
    public function __construct(
        protected VicidialCampaignStatsGateway $vicidialCampaignStatsGateway
    )
    {
    }

    public function getById(string $campaignId): CampaignStats
    {
        return $this->vicidialCampaignStatsGateway->getById($campaignId);
    }

    public function agentNonPausedByCampaign(string $campaignId): int
    {
        return $this->vicidialCampaignStatsGateway->agentNoPausedByCampaignId($campaignId);
    }
}
