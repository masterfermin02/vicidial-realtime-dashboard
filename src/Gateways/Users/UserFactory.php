<?php

namespace Phpdominicana\Lightwave\Gateways\Users;

readonly class UserFactory
{
    public function create(array $data): User
    {
        return new User(
            $data['name'],
            $data['email'],
            $data['password']
        );
    }

    public function createCollectionFromArray(array $users): UserCollection
    {
        $collection = new UserCollection($this);

        foreach ($users as $user) {
            $collection->add($this->create($user));
        }

        return $collection;
    }
}
