<?php

namespace Phpdominicana\Lightwave\Gateways\VicidialCampaigns;

use Phpdominicana\Lightwave\Database\DataBaseInterface;

final readonly class VicidialCampaignGateway
{
    public function __construct(
        public DataBaseInterface $db,
        public VicidialCampaignFactory $vicidialCampaignFactory
    )
    {
    }

    public function getRealtimeDataByCampaignIds(array $campaignIds): VicidialCampaignRealtimeStat
    {
        $selects[] = "avg(auto_dial_level) as avg_auto_dial_level";
        $selects[] = "min(dial_status_a) as min_dial_status_a";
        $selects[] = "min(dial_status_b) as min_dial_status_b";
        $selects[] = "min(dial_status_c) as min_dial_status_c";
        $selects[] = "min(dial_status_d) as min_dial_status_d";
        $selects[] = "min(dial_status_e) as min_dial_status_e";
        $selects[] = "min(lead_order) as min_lead_order";
        $selects[] = "min(lead_filter_id) as min_lead_filter_id";
        $selects[] = "sum(hopper_level) as sum_hopper_level";
        $selects[] = "min(dial_method) as min_dial_method";
        $selects[] = "avg(adaptive_maximum_level) as avg_adaptive_maximum_level";
        $selects[] = "avg(adaptive_dropped_percentage) as avg_adaptive_dropped_percentage";
        $selects[] = "avg(adaptive_dl_diff_target) as avg_adaptive_dl_diff_target";
        $selects[] = "avg(adaptive_intensity) as avg_adaptive_intensity";
        $selects[] = "min(available_only_ratio_tally) as min_available_only_ratio_tally";
        $selects[] = "min(adaptive_latest_server_time) as min_adaptive_latest_server_time";
        $selects[] = "min(local_call_time) as min_local_call_time";
        $selects[] = "avg(dial_timeout) as avg_dial_timeout";
        $selects[] = "min(dial_statuses) as min_dial_statuses";
        $selects[] = "max(agent_pause_codes_active) as max_agent_pause_codes_active";
        $selects[] = "max(list_order_mix) as max_list_order_mix";
        $selects[] = "max(auto_hopper_level) as max_auto_hopper_level";
        $selects[] = "max(ofcom_uk_drop_calc) as max_ofcom_uk_drop_calc";

        return $this->vicidialCampaignFactory->newRealtimeStaInstance(
           $this->db
               ->table('vicidial_campaigns')
               ->select($selects)
               ->whereIn('campaign_id', $campaignIds)
               ->where('active', 'Y')
               ->first()
        );
    }

}
