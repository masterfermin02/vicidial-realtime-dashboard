<?php

namespace Phpdominicana\Lightwave\Gateways\VicidialCampaignStats;

use Phpdominicana\Lightwave\Database\DataBaseInterface;

class VicidialCampaignStatsGateway
{
    public function __construct(
        protected DataBaseInterface $db,
        protected CampaignStatsFactory $campaignStatsFactory
    )
    {
    }

    public function getById(string $campaignId): CampaignStats
    {
        $selects[] = "sum(calls_today) as sum_calls_today";
        $selects[] = "sum(drops_today) as sum_drops_today";
        $selects[] = "sum(answers_today) as sum_answers_today";
        $selects[] = "max(status_category_1) as max_status_category_1";
        $selects[] = "sum(status_category_count_1) as sum_status_category_count_1";
        $selects[] = "max(status_category_2) as max_status_category_2";
        $selects[] = "sum(status_category_count_2) as sum_status_category_count_2";
        $selects[] = "max(status_category_3) as max_status_category_3";
        $selects[] = "sum(status_category_count_3) as sum_status_category_count_3";
        $selects[] = "max(status_category_4) as max_status_category_4";
        $selects[] = "sum(status_category_count_4) as sum_status_category_count_4";
        $selects[] = "sum(hold_sec_stat_one) as sum_hold_sec_stat_one";
        $selects[] = "sum(hold_sec_stat_two) as sum_hold_sec_stat_two";
        $selects[] = "sum(hold_sec_answer_calls) as sum_hold_sec_answer_calls";
        $selects[] = "sum(hold_sec_drop_calls) as sum_hold_sec_drop_calls";
        $selects[] = "sum(hold_sec_queue_calls) as sum_hold_sec_queue_calls";

        $stats = $this->db->table('vicidial_campaign_stats')
            ->select($selects)
            ->where( 'campaign_id', $campaignId)
            ->first();

        return $this->campaignStatsFactory->newInstance($stats);
    }

    public function agentNoPausedByCampaignId(string $campaignId): int
    {
       $stats = $this->db->table('vicidial_campaign_stats')
            ->select(['count(agent_non_pause_sec) as agent_no_paused'])
            ->where('campaign_id', $campaignId)
            ->first();

        return $stats['agent_no_paused'] ?? 0;
    }
}
