<?php

namespace Phpdominicana\Lightwave;

interface RequestHandlerInterface
{
    public function handleRequest() : ResponseHandlerInterface;
}
