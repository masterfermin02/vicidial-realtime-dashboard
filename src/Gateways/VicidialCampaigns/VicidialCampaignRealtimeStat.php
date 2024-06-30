<?php

namespace Phpdominicana\Lightwave\Gateways\VicidialCampaigns;

final readonly class VicidialCampaignRealtimeStat
{
    public function __construct(
        public float $avgAutoDialLevel,
        public int $minDialStatusA,
        public int $minDialStatusB,
        public int $minDialStatusC,
        public int $minDialStatusD,
        public int $minDialStatusE,
        public int $minLeadOrder,
        public int $minLeadFilterId,
        public int $sumHopperLevel,
        public int $minDialMethod,
        public float $avgAdaptiveMaximumLevel,
        public float $avgAdaptiveDroppedPercentage,
        public float $avgAdaptiveDlDiffTarget,
        public float $avgAdaptiveIntensity,
        public int $minAvailableOnlyRatioTally,
         public int $minAdaptiveLatestServerTime,
         public int $minLocalCall_time,
         public float $avgDialTimeout,
         public int $minDialStatuses,
         public int $maxAgentPauseCodesActive,
         public int $maxListOrderMix,
         public int $maxAutoHopperLevel,
         public int $maxOfcomUkDropCalc,
    ) {}
}
