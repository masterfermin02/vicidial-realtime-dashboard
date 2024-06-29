<?php

namespace Phpdominicana\Lightwave\Http;

use Phpdominicana\Lightwave\ResponseHandlerInterface;
use Symfony\Component\HttpFoundation\Response;

class SymfonyResponseHandler implements ResponseHandlerInterface
{
    public function __construct(protected Response $response)
    {
    }

    public function handleResponse() : void
    {
        $this->response->send();
    }
}
