<?php

namespace Phpdominicana\Lightwave\Gateways\VicidiaLiveAgent;

readonly class VicidialLiveAgent
{
    public function __construct(
        public string $extension,
        public string $user,
        public string $confExten,
        public string $status,
        public string $serverIp,
        public string $lastCallTime,
        public string $lastCallFinish,
        public string $callServerIp,
        public string $campaignId,
        public string $userGroup,
        public string $fullName,
        public string $comments,
        public string $callsToday,
        public string $calledId,
        public string $leadId,
        public string $lastStateChange,
        public string $onHookAgent,
        public string $ringCallerId,
        public string $agentLogId
    )
    {
    }
}
