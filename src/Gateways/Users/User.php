<?php

namespace Phpdominicana\Lightwave\Gateways\Users;

readonly class User
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    )
    {
    }
}
