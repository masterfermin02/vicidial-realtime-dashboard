<?php

namespace Phpdominicana\Lightwave\Gateways\VicidiaLiveAgent;

class VicidialLiveAgentFactory
{
    public function createFromArray(array $agents): array
    {
        $collection = [
            'agents' => [],
            'agentRing' => [],
            'agent-3-WAY'
        ];

        foreach($agents as $agent) {
            if(in_array($agent->status,["DEAD","DISPO","PAUSED"]) && $call_time_S >= 21600) {
                continue;
            }

            if($agent['on_hook_agent'] == "Y" && strlen($agents['ring_callerid']) > 18){
                $collection['agentRing'][] = $agent;
            } else if($agent['lead_id'] != 0){
                $mostrecent = $this->checkThreeWay($agents -> lead_id);
                if($mostrecent)
                    $agents -> status = "3-WAY";
            }

            if (preg_match("/READY|PAUSED/i",$agents -> status)) {
                $agents->lct = $agents->lsf;

                if ($agents->lead_id > 0) {
                    $agents->status = 'DISPO';
                }
            }

            if (preg_match("/INCALL/i",$agents -> status))
            {
                $parked_channel = $this -> getParkedCount($agents -> callerid);

                if ($parked_channel > 0)
                {
                    $agents -> status =	'PARK';
                }
                else
                {
                    if (!in_array($agents -> callerid,$this -> vcCallerIDs) && !preg_match("/EMAIL/i",$agents -> comments) && !preg_match("/CHAT/i",$agents -> comments))
                    {
                        $agents -> lct = $agents -> lsf;
                        $agents -> status =	'DEAD';
                    }
                }

                if ( (preg_match("/AUTO/i",$agents -> comments)) or (strlen($agents -> comments)<1) )
                {
                    $CM='A';
                }
                else
                {
                    if (preg_match("/INBOUND/i",$agents -> comments))
                    {
                        $CM='I';
                    }
                    else if (preg_match("/EMAIL/i",$agents -> comments))
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
            if (!preg_match("/INCALL|QUEUE|PARK|3-WAY/i",$agents -> status))
            {
                $call_time_S = ($STARTtime - $agents -> lsf);
            }
            else if (preg_match("/3-WAY/i",$agents -> status))
            {
                $call_time_S = ($STARTtime - $mostrecent);
            }
            else
            {
                $call_time_S = ($STARTtime - $agents -> lct);
            }

            $call_time_MS =		$this -> sec_convert($call_time_S,'M');
            $call_time_MS =		sprintf("%7s", $call_time_MS);
            $custPhone = "";


            //lets update agents count
            switch ($agents -> status){
                case "DEAD":
                    if($call_time_S < 21600){
                        $this -> box_agent_total++;
                        $this -> box_agent_dead++;
                    }
                    break;
                case "DISPO":
                    if($call_time_S < 21600){
                        $this -> box_agent_total++;
                        $this -> box_agent_dispo++;
                    }
                    break;
                case "PAUSED":
                    if($call_time_S < 21600){
                        $this -> box_agent_total++;
                        $this -> box_agent_paused++;
                    }
                    break;
                case "INCALL":
                case "3-WAY":
                case "QUEUE":
                    $this -> box_agent_incall++;
                    $this -> box_agent_total++;
                    $custPhone = isset($this -> vcCustPhonesArray[$agents -> lead_id]) ? $this -> vcCustPhonesArray[$agents -> lead_id] : "";

                    break;
                case "READY":
                case "CLOSER":
                    $this -> box_agent_ready++;
                    $this -> box_agent_total++;
                    break;

            }

            if($agents -> status == "PAUSED"){
                if ($agents_pause_code_active > 0)
                {
                    $pcode = $this -> getPauseCode($agents -> agent_log_id,$agents -> user);
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
            $vcAgent['extension'] = $agents -> extension;
            $vcAgent["phone"] = sprintf("%-12s",$this -> retrivePhone($agents -> extension, $agents -> server_ip));
            $vcAgent['cust_phone'] = $custPhone;
            $vcAgent['user'] = sprintf("%-20s", $agents -> user);
            $vcAgent['sessionid'] = sprintf("%-9s", $agents -> conf_exten);
            $vcAgent['status'] = $agents -> status;
            $vcAgent['serverip'] = sprintf("%-15s", $agents -> server_ip);
            $vcAgent['call_serverip'] = sprintf("%-15s", $agents -> call_server_ip);
            $vcAgent['campaign_id'] = sprintf("%-10s", $agents -> campaign_id);
            $vcAgent['comments'] = $agents -> comments;
            $vcAgent['calls_today'] = sprintf("%-5s", $agents -> calls_today);
            $vcAgent['user_group'] = sprintf("%-12s", $agents -> user_group);
            $vcAgent['full_name'] = sprintf("%-60s", $agents -> full_name);
            $vcAgent['pausecode'] = 'N/A';
            $vcAgent['call_time'] = $call_time_MS;
            $vcAgent['call_type'] = 0;
            if ($CM == 'I')
            {
                $query = \PATHAODB::table("vicidial_auto_calls");
                $query -> select(\PATHAODB::raw("count(*) as total"));
                $query->join('vicidial_inbound_groups', function($table)
                {
                    $table->on('vicidial_auto_calls.campaign_id', '=', 'vicidial_inbound_groups.group_id');
                });
                $query -> where("vicidial_auto_calls.callerid","=",$agents -> callerid);

                $result = $query -> first();

                if(!empty($result) && $result -> total > 0){
                    $vcAgent['call_type'] = 1;
                }

            }

            $this -> vcAgentsArray[] = $vcAgent;
        }

        return $collection;
    }
}
