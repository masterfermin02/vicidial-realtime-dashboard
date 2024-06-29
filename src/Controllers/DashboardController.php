<?php

namespace Phpdominicana\Lightwave\Controllers;

use Pimple\Psr11\Container;
use Symfony\Component\HttpFoundation\Response;

class DashboardController
{
    public function __construct(
        protected Container $container
    )
    {
    }
    public function index(): Response
    {
        $realtimeService = $this->container->get('RealtimeService');

        $agents = $realtimeService->getRealtimeData();
        $view = $this->container->get('view');
        return new Response($view->render('dashboard.twig', ['agents' => $agents]));
    }
}
