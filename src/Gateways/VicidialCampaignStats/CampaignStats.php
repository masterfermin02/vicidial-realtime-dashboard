<?php

namespace Phpdominicana\Lightwave\Gateways\VicidialCampaignStats;

final readonly class CampaignStats
{
    public function __construct(
       public int $sumCallsToday,
       public int $sumDropsToday,
       public int $sumAnswersToday,
       public int $maxStatusCategory1,
       public int $sumStatusCategoryCount1,
       public int $maxStatusCategory2,
       public int $sumStatusCategoryCount2,
       public int $maxStatusCategory3,
       public int $sumStatusCategoryCount3,
       public int $maxStatusCategory4,
       public int $sumStatusCategoryCount4,
       public int $sumHoldSecStatOne,
       public int $sumHoldSecStatTwo,
       public int $sumHoldSecAnswerCalls,
       public int $sumHoldSecDropCalls,
       public int $sumHoldSecQueueCalls,
    )
    {

    }
}
