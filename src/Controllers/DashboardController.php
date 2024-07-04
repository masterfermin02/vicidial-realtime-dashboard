<?php

namespace Phpdominicana\Lightwave\Controllers;

use Phpdominicana\Lightwave\SSE\Event;
use Phpdominicana\Lightwave\SSE\SSE;
use Phpdominicana\Lightwave\SSE\StopSSEException;
use Pimple\Psr11\Container;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController
{
    public function __construct(
        protected Container $container
    )
    {
    }
    public function index(): Response
    {
        $view = $this->container->get('view');
        return new Response($view->render('dashboard.twig', []));
    }

    public function sse(): StreamedResponse
    {
        $response = new \Symfony\Component\HttpFoundation\StreamedResponse();
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no'); // Nginx: unbuffered responses suitable for Comet and HTTP streaming applications
        $response->setCallback(function () {
            $callback = function () {
                $id = mt_rand(1, 1000);
                $realtimeService = $this->container->get('RealtimeService');
                $realtimeStats = $realtimeService->getRealtimeData();
                $realtimeData = ['id' => $id, 'title' => 'title ' . $id, 'content' => $realtimeStats]; // Get realtime from database or service.
                if (empty($realtimeData)) {
                    return false; // Return false if no new messages
                }
                $shouldStop = false; // Stop if something happens or to clear connection, browser will retry
                if ($shouldStop) {
                    throw new StopSSEException();
                }
                return json_encode(compact('realtimeData'));
            };
            (new SSE(new Event($callback, 'realtimeData')))->start(10);
        });
        return $response;
    }
}
