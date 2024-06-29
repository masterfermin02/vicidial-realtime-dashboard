<?php

namespace Phpdominicana\Lightwave\Gateways\VicidiaLiveAgent;

use Pimple\Psr11\Container;
use Phpdominicana\Lightwave\Gateways\Dashboard;

class VicidialLiveAgentGateway
{
    public function __construct(
        protected Container $container
    )
    {
    }

    public function getAgents(): array
    {
        $vicidialLiveAgentFactory = $this->container->get('VicidialLiveAgentFactory');
        $database = $this->container->get('pdo');
        $query = $database->prepare('select extension,vicidial_live_agents.user,conf_exten,vicidial_live_agents.status,vicidial_live_agents.server_ip,
        UNIX_TIMESTAMP(last_call_time) as lct,UNIX_TIMESTAMP(last_call_finish) as lcf,call_server_ip,vicidial_live_agents.campaign_id,
        vicidial_users.user_group,vicidial_users.full_name,vicidial_live_agents.comments,vicidial_live_agents.calls_today,
        vicidial_live_agents.callerid,lead_id,UNIX_TIMESTAMP(last_state_change) as lsf,on_hook_agent,ring_callerid,agent_log_id
        from vicidial_live_agents
        join vicidial_users on vicidial_live_agents.user=vicidial_users.user
        ');
        $query->execute();

        return $vicidialLiveAgentFactory->createFromArray($query->fetchAll());
    }
}
