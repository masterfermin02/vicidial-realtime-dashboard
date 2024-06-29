<?php

namespace Phpdominicana\Lightwave\Providers;

use Phpdominicana\Lightwave\Application;
use Phpdominicana\Lightwave\Http\SymfonyRequestHandler;
use Pimple\Psr11\Container;

class RequestServiceProvider implements ProviderInterface
{

    #[\Override] public function register(Application $app): void
    {
        $app->getContainer()['RequestHandlerInterface'] = fn () => new SymfonyRequestHandler(
            new Container($app->getContainer())
        );
    }
}
