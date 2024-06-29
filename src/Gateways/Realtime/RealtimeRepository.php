<?php

namespace Phpdominicana\Lightwave\Gateways\Realtime;

use Phpdominicana\Lightwave\Gateways\VicidialCampaignStats\VicidialCampaignStatsRepository;
use Pimple\Psr11\Container;

final class RealtimeRepository
{
    public VicidialCampaignStatsRepository $vicidialCampaignStatsRepository;
    public function __construct(
        public string $isInbound,
        public array $miniStatsArray,
        public array $vcAgentsArray,
        public array $vcWaitingList,
        public bool $isVSCAT,
        public string $closerCampaign,
        public Container $container,
    )
    {
        $this->vicidialCampaignStatsRepository = $this->container->get('VicidialCampaignStatsRepository');
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

    }

    public function callStatus(): void
    {

    }

    public function listCallerIDs(): void
    {

    }

    public function listAgents(): void
    {

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
