<?php
namespace SocymSlim\MVC\controllers;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Container\ContainerInterface;
use Slim\Views\Twig;

class MemberController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    function twig(): Twig
    {
        return $this->container->get("view");
    }

    public function goMemberAdd(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $response = $this->twig()->render($response, "memberAdd.html");
        return $response;
    }
}

