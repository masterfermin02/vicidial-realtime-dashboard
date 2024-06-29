<?php

namespace Phpdominicana\Lightwave\Providers;

class PdoDatabaseProvider implements ProviderInterface
{
    public function register(\Phpdominicana\Lightwave\Application $app): void
    {
        $database = $app->getConfig()->get('database');
        $connection = $database['connections'][$database['default']];
        $dns = $connection['driver'] . ':host=' . $connection['host'] . ';port=' . $connection['port'] . ';dbname=' . $connection['database'];
        $app->getContainer()['pdo'] = fn () => new \PDO($dns, $connection['username'], $connection['password']);
    }
}
