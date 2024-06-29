<?php

namespace Phpdominicana\Lightwave\Controllers;

use Pimple\Psr11\Container;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class LoginController
{
    public static function index(Container $container): Response
    {
        $view = $container->get('view');
        return new Response($view->render('login.twig'));
    }

    public static function login(Container $container): Response
    {
        $userService = $container->get('UserService');
        $userService->login($_POST['username'], $_POST['password']);

        return new RedirectResponse('/');
    }
}
