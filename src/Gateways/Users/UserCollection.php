<?php

namespace Phpdominicana\Lightwave\Gateways\Users;

final readonly class UserCollection
{
    /**
     * @param UserFactory $factory
     * @param User[] $users
     */
    public function __construct(
        public UserFactory $factory,
        public array $users = [],
    )
    {
    }

    public function add(User $user): self
    {
        return new static(
            $this->factory,
            array_merge($this->users, [$user])
        );
    }

    public function all(): array
    {
        return $this->users;
    }
}
