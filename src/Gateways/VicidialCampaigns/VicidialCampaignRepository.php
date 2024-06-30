<?php

namespace Phpdominicana\Lightwave\Gateways\VicidialCampaigns;

class VicidialCampaignRepository
{
    public function __construct(
        protected VicidialCampaignGateway $vicidialCampaignGateway
    )
    {
    }

    public function getRealtimeDataByCampaignIds(array $campaignIds): VicidialCampaignRealtimeStat
    {
        return $this->vicidialCampaignGateway->getRealtimeDataByCampaignIds($campaignIds);
    }
}
