<?php

namespace Phpdominicana\Lightwave\Http;

use Phpdominicana\Lightwave\DelegateInterface;
use Phpdominicana\Lightwave\DelegatorInterface;
use Phpdominicana\Lightwave\RequestHandlerInterface;
use Phpdominicana\Lightwave\ResponseHandlerInterface;
use Throwable;

class RequestHandler implements RequestHandlerInterface
{
    public function __construct(
        protected DelegatorInterface $delegator,
        protected ResponseHandlerFactory $responseHandlerFactory,
    ) {
    }

    /**
     * @throws FrontInteropException
     */
    public function handleRequest() : ResponseHandlerInterface
    {
        try {
            $delegate = $this->delegator->delegateRequest();
            $response = $this->getResponse($delegate);
        } catch (Throwable $e) {
            $delegate = $this->delegator->delegateThrowable($e);
            $response = $this->getResponse($delegate);
        }

        return $this->responseHandlerFactory->newResponseHandler($response);
    }

    protected function getResponse(DelegateInterface $delegate) : object
    {
        $callable = $delegate->getCallable();
        $arguments = $delegate->getArguments();
        return $callable(...$arguments);
    }
}
