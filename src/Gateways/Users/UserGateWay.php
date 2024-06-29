<?php

namespace Phpdominicana\Lightwave\Gateways\Users;

use Pimple\Psr11\Container;

final readonly class UserGateWay
{
    private \PDO $pdo;
    private UserFactory $factory;

    public function __construct(
        Container $container
    )
    {
        $this->pdo = $container->get('pdo');
        $this->factory = $container->get('userFactory');

    }

    public function create(string $name, string $email, string $password): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO users (name, email, password) VALUES (:name, :email, :password)');
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);
    }

    public function findById(int $id): User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $this->factory->createCollectionFromArray($stmt->fetch());
    }

    public function findByUserName(string $name): User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE name = :name');
        $stmt->execute(['name' => $name]);
        return $this->factory->create($stmt->fetch());
    }

    public function all(): UserCollection
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users');
        $stmt->execute();
        return $this->factory->createFromArray($stmt->fetchAll());
    }
}
