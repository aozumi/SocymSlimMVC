<?php
namespace SocymSlim\MVC\exceptions;

use Throwable;
use Psr\Container\ContainerInterface;
use Slim\Interfaces\ErrorRendererInterface;
use Slim\Views\Twig;

class CustomErrorRenderer implements ErrorRendererInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Throwable $exception, bool $displayErrorDetails): string
    {
        $twig = $this->container->get("view");
        $msg = 'もう一度始めから操作してください。';
        $html = $twig->fetch("error.html", ["errorMsg" => $msg]);
        return $html;
    }
}
