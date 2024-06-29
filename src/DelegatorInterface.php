<?php

namespace Phpdominicana\Lightwave;

use Throwable;

interface DelegatorInterface
{
    public function delegateRequest() : DelegateInterface;

    public function delegateThrowable(Throwable $e) : DelegateInterface;
}
