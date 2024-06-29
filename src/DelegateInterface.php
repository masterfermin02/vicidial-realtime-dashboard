<?php

namespace Phpdominicana\Lightwave;

interface DelegateInterface
{
    public function getCallable() : callable;

    /**
     * @return mixed[]
     */
    public function getArguments() : array;
}
