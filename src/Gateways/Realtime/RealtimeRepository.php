<?php

namespace Phpdominicana\Lightwave\Gateways\Realtime;

use Phpdominicana\Lightwave\Database\DataBaseInterface;
use Phpdominicana\Lightwave\Gateways\VicidialCampaigns\VicidialCampaignRepository;
use Phpdominicana\Lightwave\Gateways\VicidialCampaignStats\VicidialCampaignStatsRepository;
use Pimple\Psr11\Container;

final class RealtimeRepository
{
    protected VicidialCampaignStatsRepository $vicidialCampaignStatsRepository;
    protected VicidialCampaignRepository $vicidialCampaignRepository;

    protected DataBaseInterface $db;

    protected int $boxOutLive = 0;
    protected int $boxAgentonlycount = 0;
    protected int $boxInIvr = 0;
    protected int $boxOutRing = 0;
    protected int $boxOutTotal = 0;

    protected array $vcCallerIDs = [];
    protected array $vcCustPhonesArray = [];

    protected int $boxRingAgents = 0;

    public function __construct(
        protected string $isInbound,
        protected array $miniStatsArray,
        protected array $vcAgentsArray,
        protected array $vcWaitingList,
        protected bool $isVSCAT,
        protected string $closerCampaign,
        protected Container $container,
        protected array $selectedCampaignIds = [],
        protected bool $returnAllActive = true,
    )
    {
        $this->vicidialCampaignStatsRepository = $this->container->get('VicidialCampaignStatsRepository');
        $this->vicidialCampaignRepository = $this->container->get('VicidialCampaignRepository');
        $this->db = $this->container->get('DataBaseInterface');
    }

    public function inboundOnlyMiniStats(): void
    {
        $result = $this->vicidialCampaignStatsRepository->getById($this->closerCampaign);

        $this->miniStatsArray['calls_today'] = $result->sumCallsToday;
        $this->miniStatsArray['drops_today'] = round($result->sumDropsToday);
        $this->miniStatsArray['answers_today'] = $result->sumAnswersToday;
        $this->miniStatsArray['outbound_today'] = $this->miniStatsArray['calls_today'] - ($this->miniStatsArray['drops_today'] + $this->miniStatsArray['answers_today']);

        if ($this->isVSCAT) {
            $this->miniStatsArray['max_status_category_1'] = $result->maxStatusCategory1;
            $this->miniStatsArray['sum_status_category_count_1'] = $result->sumStatusCategoryCount1;
            $this->miniStatsArray['max_status_category_2'] = $result->maxStatusCategory2;
            $this->miniStatsArray['sum_status_category_count_2'] = $result->sumStatusCategoryCount2;
            $this->miniStatsArray['max_status_category_3'] = $result->maxStatusCategory3;
            $this->miniStatsArray['sum_status_category_count_3'] = $result->sumStatusCategoryCount3;
            $this->miniStatsArray['max_status_category_4'] = $result->maxStatusCategory4;
            $this->miniStatsArray['sum_status_category_count_4'] = $result->sumStatusCategoryCount4;
        }

        $this->miniStatsArray['drop_percent'] = sprintf("%01.2f",
            round(($this->MathZDC($this->miniStatsArray['drops_today'], $this->miniStatsArray['answers_today']) * 100), 2)
        );


        $this->miniStatsArray['avg_hold_queue'] = round($this->MathZDC($result->sumHoldSecQueueCalls, $this->miniStatsArray['calls_today']), 0);
        $this->miniStatsArray['avg_drop_queue'] = round($this->MathZDC($result->sumHoldSecDropCalls, $this->miniStatsArray['drops_today']), 0);

        $this->miniStatsArray['tma1'] = sprintf("%01.2f",
            round(($this->MathZDC($result->sumHoldSecStatOne, $this->miniStatsArray['answers_today']) * 100), 2)
        );

        $this->miniStatsArray['tma2'] = sprintf("%01.2f",
            round(($this->MathZDC($result->sumHoldSecStatTwo, $this->miniStatsArray['answers_today']) * 100), 2)
        );

        $this->miniStatsArray['avg_hold_answered'] = round($this->MathZDC($result->sumHoldSecAnswerCalls, $this->miniStatsArray['answers_today']), 0);

        $this->miniStatsArray['avg_answer_agent_nonpaused'] = sprintf("%01.2f",
            round(($this->MathZDC($this->miniStatsArray['answers_today'], $this->agentNonPaused()) * 60), 2)
        );
    }

    public function completeMiniStats(): void
    {
        $viciResult = $this->vicidialCampaignRepository->getRealtimeDataByCampaignIds(
            !$this->returnAllActive && $this->isInbound == "YES"
                ? array_merge($this->selectedCampaignIds, [$this->closerCampaign])
                : $this->selectedCampaignIds
        );

        $selects = [];
        $selects[] = "sum(dialable_leads) as sum_dialable_leads";
        $selects[] = "sum(calls_today) as sum_calls_today";
        $selects[] = "sum(drops_today) as sum_drops_today";

        $selects[] = "avg(drops_answers_today_pct) as avg_drops_answers_today_pct";
        $selects[] = "avg(differential_onemin) as avg_differential_onemin";
        $selects[] = "avg(agents_average_onemin) as avg_agents_average_onemin";


        $selects[] = "sum(balance_trunk_fill) as sum_balance_trunk_fill";
        $selects[] = "sum(answers_today) as sum_answers_today";

        if ($this->isVSCAT) {
            $selects[] = "max(status_category_1) as max_status_category_1";
            $selects[] = "sum(status_category_count_1) as sum_status_category_count_1";
            $selects[] = "max(status_category_2) as max_status_category_2";
            $selects[] = "sum(status_category_count_2) as sum_status_category_count_2";
            $selects[] = "max(status_category_3) as max_status_category_3";
            $selects[] = "sum(status_category_count_3) as sum_status_category_count_3";
            $selects[] = "max(status_category_4) as max_status_category_4";
            $selects[] = "sum(status_category_count_4) as sum_status_category_count_4";
        }

        $selects[] = "sum(agent_calls_today) as sum_agent_calls_today";
        $selects[] = "sum(agent_wait_today) as sum_agent_wait_today";
        $selects[] = "sum(agent_custtalk_today) as sum_agent_custtalk_today";
        $selects[] = "sum(agent_acw_today) as sum_agent_acw_today";
        $selects[] = "sum(agent_pause_today) as sum_agent_pause_today";
        $selects[] = "sum(agenthandled_today) as sum_agenthandled_today";

        $query = $this->db->table("vicidial_campaign_stats")
            ->select($selects)
            ->whereIn("campaign_id", $this->selectedCampaignIds)
            ->where("calls_today", ">", "-1");

        if ($this->isInbound == "YES") {
            $query->whereIn("campaign_id", array_merge($this->selectedCampaignIds, [$this->closerCampaign]));
        } else {
            $query->whereIn("campaign_id", $this->selectedCampaignIds);
        }
        $viciStatsResult = $query->first();

        $this->miniStatsArray['dial_level'] = sprintf("%01.3f", $viciResult->avgAutoDialLevel);
        $this->miniStatsArray['trunk_short_fill'] = 0;
        $this->miniStatsArray['dial_filter'] = $viciResult->minLeadFilterId;
        $this->miniStatsArray['dialable_leads'] = $viciStatsResult['sum_dialable_leads'] ?? 0;
        $this->miniStatsArray['calls_today'] = $viciStatsResult['sum_calls_today'] ?? 0;
        $this->miniStatsArray['avg_agents'] = $viciStatsResult['sum_agent_calls_today'] ?? 0;
        $this->miniStatsArray['dial_method'] = $viciResult->minDialMethod;
        $this->miniStatsArray['hopper'] = $viciResult->sumHopperLevel . "/" . $viciResult->maxAutoHopperLevel;
        $this->miniStatsArray['dropped'] = $viciStatsResult['sum_drops_today'] . "/" . $viciStatsResult['sum_answers_today'];
        $this->miniStatsArray['drops_today'] = round($viciStatsResult['sum_drops_today'] ?? 0);
        $this->miniStatsArray['answers_today'] = $viciStatsResult['sum_answers_today'] ?? 0;
        $this->miniStatsArray['dl_diff'] = sprintf("%01.2f", $viciStatsResult['avg_differential_onemin']);
        $this->miniStatsArray['statuses'] = $viciResult->minDialStatuses;

        $this->miniStatsArray['outbound_today'] = $this->miniStatsArray['calls_today'] - ($this->miniStatsArray['drops_today'] + $this->miniStatsArray['answers_today']);


        $this->miniStatsArray['leads_in_hopper'] = 0;
        $this->miniStatsArray['drop_percent'] = $viciStatsResult['sum_drops_today'];

        $this->miniStatsArray['drop_percent'] = sprintf("%01.2f",
            round(($this->MathZDC($viciStatsResult['sum_drops_today'], $viciStatsResult['sum_answers_today']) * 100), 2)
        );
        $this->miniStatsArray['diff'] = sprintf("%01.2f",
            round(($this->MathZDC($viciStatsResult['avg_differential_onemin'], $viciStatsResult['avg_agents_average_onemin']) * 100), 2)
        );
        $this->miniStatsArray['order'] = $viciResult->minLeadOrder;
    }

    public function callStatus(): void
    {
        $results = $this->db->table("vicidial_auto_calls")
            ->select(["status,campaign_id,phone_number,server_ip,UNIX_TIMESTAMP(call_time) as unix_call_time,call_type,queue_priority,agent_only"])
            ->whereNotIn("status",['XFER'])
            ->whereIn("campaign_id", $this->selectedCampaignIds)
            ->get();

        $STARTtime = date("U");

        if(!empty($results)){
            foreach($results as $callData){
                if($callData['status'] == "LIVE"){
                    $this->boxOutLive++;

                    $arr = ["status" => $callData['status'],
                        "campaign" => $callData['campaign_id'],
                        "phone" => $callData['phone_number'],
                        "serverip" => $callData['server_ip'],
                        "dialtime" => $this->sec_convert($STARTtime - $callData['unix_call_time'], "M"),
                        "call_type" => $callData['call_type'],
                        "priority" => $callData['queue_priority']];
                    $this->vcWaitingList[] = $arr;

                    if ($callData['agent_only'] > 0) {
                        $this->boxAgentonlycount++;
                    }

                } else {
                    if ($callData['status'] == "IVR") {
                        $this->boxInIvr++;

                        $arr = ["status" => $callData['status'],
                            "campaign" => $callData['campaign_id'],
                            "phone" => $callData['phone_number'],
                            "serverip" => $callData['server_ip'],
                            "dialtime" => $this->sec_convert($STARTtime - $callData['unix_call_time'], "M"),
                            "call_type" => $callData['call_type'],
                            "priority" => $callData['queue_priority']];
                        $this->vcWaitingList[] = $arr;

                        if ($callData['agent_only'] > 0) {
                            $this->boxAgentonlycount++;
                        }
                    }
                    if ($callData['status'] !== "CLOSER"){
                        $this->boxOutRing++;
                    }
                }
                $this->boxOutTotal++;
            }
        }
    }

    public function listCallerIDs(): void
    {
        $result = $this->db
            ->table("vicidial_auto_calls")
            ->select(["callerid","lead_id","phone_number"])
            ->get();
        foreach ($result as $call) {
            $this->vcCallerIDs[] = $call['callerid'];
            $this->vcCustPhonesArray[$call['lead_id']] = $call['phone_number'];
        }
    }

    public function getAgentPaused(): int
    {
        $result =  $this->db
            ->table('vicidial_campaigns')
            ->where('agent_pause_codes_active', '!=', 'N')
            ->whereIn('campaign_id', $this->selectedCampaignIds)
            ->get();

        if (count($result) > 0) {
            return $result[0]['totalPaused'];
        }

        return 0;
    }

    public function listAgents(): void
    {
        $query = $this->db->table("vicidial_live_agents");
        $results = $query->query("select extension,vicidial_live_agents.user,conf_exten,vicidial_live_agents.status,vicidial_live_agents.server_ip,
        UNIX_TIMESTAMP(last_call_time) as lct,UNIX_TIMESTAMP(last_call_finish) as lcf,call_server_ip,vicidial_live_agents.campaign_id,
        vicidial_users.user_group,vicidial_users.full_name,vicidial_live_agents.comments,vicidial_live_agents.calls_today,
        vicidial_live_agents.callerid,lead_id,UNIX_TIMESTAMP(last_state_change) as lsf,on_hook_agent,ring_callerid,agent_log_id
        from vicidial_live_agents
        join vicidial_users on vicidial_users.user = vicidial_live_agents.user
        where vicidial_users.user_hide_realtime='0'");
        /**
         * $agents -> extension
         * $agents -> user
         * $agents -> conf_exten
         * $agents -> status
         * $agents -> server_ip
         * $agents -> lct
         * $agents -> lcf
         * $agents -> call_server_ip
         * $agents -> campaign_id
         * $agents -> user_group
         * $agents -> full_name
         * $agents -> comments
         * $agents -> calls_today
         * $agents -> callerid
         * $agents -> lead_id
         * $agents -> lsf
         * $agents -> on_hook_agent
         * $agents -> ring_callerid
         * $agents -> agent_log_id
         */

        $agents_pause_code_active = $this->getAgentPaused();

        foreach($results as $agents){

            if($agents['on_hook_agent'] == "Y"){
                $this->boxRingAgents++;
                if (strlen($agents -> ring_callerid) > 18)
                    $agents -> status = "RING";
            }
            if($agents['lead_id'] != 0){
                $mostrecent = $this->checkThreeWay($agents['lead_id']);
                if ($mostrecent) {
                    $agents['status'] = "3-WAY";
                }
            }

            if (preg_match("/READY|PAUSED/i",$agents['status']))
            {
                $agents['lct'] = $agents['lsf'];

                if ($agents['lead_id'] > 0)
                {
                    $agents['status'] =	'DISPO';
                }
            }

            if($agents_pause_code_active > 0){
                $pausecode = 'N/A';
            }else{
                $pausecode = 'N/A';
            }

            if (preg_match("/INCALL/i",$agents['status']))
            {
                $parked_channel = $this->getParkedCount($agents['callerid']);

                if ($parked_channel > 0)
                {
                    $agents['status'] =	'PARK';
                }
                else
                {
                    if (!in_array($agents['callerid'], $this->vcCallerIDs) && !preg_match("/EMAIL/i",$agents['comments']) && !preg_match("/CHAT/i",$agents['comments']))
                    {
                        $agents['lct'] = $agents['lsf'];
                        $agents['status'] =	'DEAD';
                    }
                }

                if ( (preg_match("/AUTO/i",$agents['comments'])) or (strlen($agents['comments'])<1) )
                {
                    $CM='A';
                }
                else
                {
                    if (preg_match("/INBOUND/i",$agents['comments']))
                    {
                        $CM='I';
                    }
                    else if (preg_match("/EMAIL/i",$agents['comments']))
                    {
                        $CM='E';
                    }
                    else
                    {
                        $CM='M';
                    }
                }
            }
            else {
                $CM=' ';
            }

            $STARTtime = date("U");
            $call_time_S = 0;
            if (!preg_match("/INCALL|QUEUE|PARK|3-WAY/i",$agents['status']))
            {
                $call_time_S = ($STARTtime - $agents['lsf']);
            }
            else if (preg_match("/3-WAY/i",$agents['status']))
            {
                $call_time_S = ($STARTtime - $mostrecent);
            }
            else
            {
                $call_time_S = ($STARTtime - $agents['lct']);
            }

            $call_time_MS =		$this -> sec_convert($call_time_S,'M');
            $call_time_MS =		sprintf("%7s", $call_time_MS);
            $custPhone = "";


            //lets update agents count
            switch ($agents['status']){
                case "DEAD":
                    if($call_time_S < 21600){
                        $this->boxAgentTotal++;
                        $this-> boxAgentDead++;
                    }
                    break;
                case "DISPO":
                    if($call_time_S < 21600){
                        $this -> boxAgentTotal++;
                        $this -> boxAgentDispo++;
                    }
                    break;
                case "PAUSED":
                    if($call_time_S < 21600){
                        $this -> boxAgentTotal++;
                        $this -> boxAgentPaused++;
                    }
                    break;
                case "INCALL":
                case "3-WAY":
                case "QUEUE":
                    $this -> boxAgentIncall++;
                    $this -> box_agent_total++;
                    $custPhone = isset($this->vcCustPhonesArray[$agents['lead_id']]) ? $this->vcCustPhonesArray[$agents['lead_id']] : "";

                    break;
                case "READY":
                case "CLOSER":
                    $this->boxAgentReady++;
                    $this->boxAgentTotal++;
                    break;

            }

            if(in_array($agents['status'],["DEAD","DISPO","PAUSED"]) && $call_time_S >= 21600) continue;

            if($agents['status'] == "PAUSED"){
                if ($agents_pause_code_active > 0)
                {
                    $pcode = $this->getPauseCode($agents['agent_log_id'],$agents['user']);
                    if($pcode && !empty($pcode))
                        $pausecode = sprintf("%-6s", $pcode);
                    else
                        $pausecode = "N/A";
                }
                else
                {
                    $pausecode='N/A';
                }
            }


            $vcAgent = [];
            $vcAgent['extension'] = $agents['extension'];
            $vcAgent["phone"] = sprintf("%-12s",$this->retrivePhone($agents['extension'], $agents['server_ip']));
            $vcAgent['cust_phone'] = $custPhone;
            $vcAgent['user'] = sprintf("%-20s", $agents['user']);
            $vcAgent['sessionid'] = sprintf("%-9s", $agents['conf_exten']);
            $vcAgent['status'] = $agents['status'];
            $vcAgent['serverip'] = sprintf("%-15s", $agents['server_ip']);
            $vcAgent['call_serverip'] = sprintf("%-15s", $agents['call_server_ip']);
            $vcAgent['campaign_id'] = sprintf("%-10s", $agents['campaign_id']);
            $vcAgent['comments'] = $agents['comments'];
            $vcAgent['calls_today'] = sprintf("%-5s", $agents['calls_today']);
            $vcAgent['user_group'] = sprintf("%-12s", $agents['user_group']);
            $vcAgent['full_name'] = sprintf("%-60s", $agents['full_name']);
            $vcAgent['pausecode'] = $pausecode;
            $vcAgent['call_time'] = $call_time_MS;
            $vcAgent['call_type'] = 0;

            $this -> vcAgentsArray[] = $vcAgent;
        }
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

    private function checkThreeWay($lead_id){
        return false;
    }

    private function agentNonPaused()
    {
        return $this->vicidialCampaignStatsRepository->agentNonPausedByCampaign($this->closerCampaign);

    }

    private function MathZDC($dividend, $divisor, $quotient = 0)
    {
        if ($divisor == 0) {
            return $quotient;
        } else if ($dividend == 0) {
            return 0;
        } else {
            return ($dividend / $divisor);
        }
    }

    private function sec_convert($sec,$precision)
    {
        $sec = round($sec,0);

        if ($sec < 1)
        {
            if ($precision == 'HF' || $precision == 'H')
            {return "0:00:00";}
            else
            {
                if ($precision == 'S')
                {return "0";}
                else
                {return "0:00";}

            }
        }
        else
        {
            if ($precision == 'HF')
            {$precision='H';}
            else
            {
                # if ( ($sec < 3600) and ($precision != 'S') ) {$precision='M';}
            }

            if ($precision == 'H')
            {
                $Fhours_H =	$this -> MathZDC($sec, 3600);
                $Fhours_H_int = floor($Fhours_H);
                $Fhours_H_int = intval("$Fhours_H_int");
                $Fhours_M = ($Fhours_H - $Fhours_H_int);
                $Fhours_M = ($Fhours_M * 60);
                $Fhours_M_int = floor($Fhours_M);
                $Fhours_M_int = intval("$Fhours_M_int");
                $Fhours_S = ($Fhours_M - $Fhours_M_int);
                $Fhours_S = ($Fhours_S * 60);
                $Fhours_S = round($Fhours_S, 0);
                if ($Fhours_S < 10) {$Fhours_S = "0$Fhours_S";}
                if ($Fhours_M_int < 10) {$Fhours_M_int = "0$Fhours_M_int";}
                $Ftime = "$Fhours_H_int:$Fhours_M_int:$Fhours_S";
            }
            if ($precision == 'M')
            {
                $Fminutes_M = $this -> MathZDC($sec, 60);
                $Fminutes_M_int = floor($Fminutes_M);
                $Fminutes_M_int = intval("$Fminutes_M_int");
                $Fminutes_S = ($Fminutes_M - $Fminutes_M_int);
                $Fminutes_S = ($Fminutes_S * 60);
                $Fminutes_S = round($Fminutes_S, 0);
                if ($Fminutes_S < 10) {$Fminutes_S = "0$Fminutes_S";}
                $Ftime = "$Fminutes_M_int:$Fminutes_S";
            }
            if ($precision == 'S')
            {
                $Ftime = $sec;
            }
            return "$Ftime";
        }
    }
}
