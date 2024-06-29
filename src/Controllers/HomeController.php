<?php

namespace Phpdominicana\Lightwave\Controllers;

use Pimple\Psr11\Container;
use Symfony\Component\HttpFoundation\Response;

readonly class HomeController
{
    public function __construct(
        protected Container $container
    )
    {
    }
    public function index(): Response
    {
        $view = $this->container->get('view');
        return new Response($view->render('welcome.twig'));
    }
}
