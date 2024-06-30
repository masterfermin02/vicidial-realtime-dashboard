<?php

namespace Phpdominicana\Lightwave\Gateways\VicidialCampaigns;

class VicidialCampaignFactory
{
    public function newRealtimeStaInstance(array $data): VicidialCampaignRealtimeStat
    {
        return new VicidialCampaignRealtimeStat(
            $data['avg_auto_dial_level'] ?? 0,
                $data['min_dial_status_a'] ?? 0,
$data['min_dial_status_b']  ?? 0,
$data['min_dial_status_c'] ?? 0,
$data['min_dial_status_d'] ?? 0,
$data['min_dial_status_e'] ?? 0,
$data['min_lead_order'] ?? 0,
$data['min_lead_filter_id'] ?? 0,
$data['sum_hopper_level'] ?? 0,
$data['min_dial_method'] ?? 0,
$data['avg_adaptive_maximum_level'] ?? 0,
$data['avg_adaptive_dropped_percentage'] ?? 0,
$data['avg_adaptive_dl_diff_target'] ?? 0,
$data['avg_adaptive_intensity'] ?? 0,
$data['min_available_only_ratio_tally'] ?? 0,
$data['min_adaptive_latest_server_time'] ?? 0,
$data['min_local_call_time'] ?? 0,
$data['avg_dial_timeout'] ?? 0,
$data['min_dial_statuses'] ?? 0,
$data['max_agent_pause_codes_active'] ?? 0,
$data['max_list_order_mix'] ?? 0,
$data['max_auto_hopper_level'] ?? 0,
$data['max_ofcom_uk_drop_calc'] ?? 0,
        );
    }
}
