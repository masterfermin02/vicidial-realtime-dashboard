<?php

namespace Phpdominicana\Lightwave\SSE;

class SSE
{
    protected Event $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    /**
     * Start SSE Server
     * @param int $interval in seconds
     */
    public function start(int $interval = 3)
    {
        while (true) {
            try {
                echo $this->event->fill();
            } catch (StopSSEException $e) {
                return;
            }

            if (ob_get_level() > 0) {
                ob_flush();
            }

            flush();

            // if the connection has been closed by the client we better exit the loop
            if (connection_aborted()) {
                return;
            }
            sleep($interval);
        }
    }
}
