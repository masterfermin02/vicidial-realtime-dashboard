<?php

namespace Phpdominicana\Lightwave\Http;

use Phpdominicana\Lightwave\ResponseHandlerInterface;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ResponseHandlerFactory
{
    public function newResponseHandler(object $response) : ResponseHandlerInterface
    {
        switch (true) {
            case $response instanceof PsrResponse:
                return new PsrResponseHandler($response);

            case $response instanceof SymfonyResponse:
                return new SymfonyResponseHandler($response);

            default:
                $type = get_class($response);

                throw new FrontInteropException("Unknown response type: {$type}");
        }
    }
}
