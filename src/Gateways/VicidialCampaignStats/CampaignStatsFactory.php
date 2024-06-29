<?php

namespace Phpdominicana\Lightwave\Gateways\VicidialCampaignStats;

class CampaignStatsFactory
{
    public function newInstance(array $data): CampaignStats
    {
        return new CampaignStats(
            sumCallsToday: $data['sum_calls_today'] ?? 0,
            sumDropsToday: $data['sum_drops_today'] ?? 0,
            sumAnswersToday: $data['sum_answers_today'] ?? 0,
            maxStatusCategory1: $data['max_status_category_1'] ?? 0,
            sumStatusCategoryCount1: $data['sum_status_category_count_1'] ?? 0,
            maxStatusCategory2: $data['max_status_category_2'] ?? 0,
            sumStatusCategoryCount2: $data['sum_status_category_count_2'] ?? 0,
            maxStatusCategory3: $data['max_status_category_3'] ?? 0,
            sumStatusCategoryCount3: $data['sum_status_category_count_3'] ?? 0,
            maxStatusCategory4: $data['max_status_category_4'] ?? 0,
            sumStatusCategoryCount4: $data['sum_status_category_count_4'] ?? 0,
            sumHoldSecStatOne: $data['sum_hold_sec_stat_one'] ?? 0,
            sumHoldSecStatTwo: $data['sum_hold_sec_stat_two'] ?? 0,
            sumHoldSecAnswerCalls: $data['sum_hold_sec_answer_calls'] ?? 0,
            sumHoldSecDropCalls: $data['sum_hold_sec_drop_calls'] ?? 0,
            sumHoldSecQueueCalls: $data['sum_hold_sec_queue_calls'] ?? 0,
        );
    }

    public function newCollection(array $data): array
    {
        $collection = [];

        foreach ($data as $item) {
            $collection[] = $this->newInstance($item);
        }

        return $collection;
    }
}
