<?php

namespace Phpdominicana\Lightwave\Http;

use Phpdominicana\Lightwave\ResponseHandlerInterface;
use Psr\Http\Message\ResponseInterface;

class PsrResponseHandler implements ResponseHandlerInterface
{
    public function __construct(
        protected ResponseInterface $response
    )
    {
    }
    public function handleResponse() : void
    {
        $this->sendHeaders();
        $this->sendStatus();
        $this->sendBody();
    }

    protected function sendHeaders() : void
    {
        foreach ($this->response->getHeaders() as $label => $values) {
            $lower = strtolower($label);
            $first = $lower !== 'set-cookie';

            foreach ($values as $value) {
                header("{$label}: {$value}", $first);
                $first = false;
            }
        }
    }

    protected function sendStatus() : void
    {
        header(
            sprintf(
                'HTTP/%s %s %s',
                $this->response->getProtocolVersion(),
                $this->response->getStatusCode(),
                $this->response->getReasonPhrase(),
            ),
            true,
            $this->response->getStatusCode(),
        );
    }

    protected function sendBody() : void
    {
        $body = $this->response->getBody();

        if ($body->isSeekable()) {
            $body->rewind();
        }

        echo $body;
    }
}
