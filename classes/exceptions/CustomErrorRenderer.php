<?php
namespace SocymSlim\MVC\exceptions;

use Throwable;
use Psr\Container\ContainerInterface;
use Slim\Interfaces\ErrorRendererInterface;
use Slim\Views\Twig;
use Slim\Error\Renderers\HtmlErrorRenderer;
use Slim\Exception\HttpNotFoundException;

class CustomErrorRenderer implements ErrorRendererInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Throwable $exception, bool $displayErrorDetails): string
    {
        if ($exception instanceof HttpNotFoundException) {
            return $this->container->get("view")->fetch("404.html");
        }

        if ($displayErrorDetails) {
            return $this->renderDefaultErrorHtml($exception, $displayErrorDetails);
        }

        $twig = $this->container->get("view");
        $msg = 'もう一度始めから操作してください。';
        $html = $twig->fetch("error.html", ["errorMsg" => $msg]);
        return $html;
    }

    public function renderDefaultErrorHtml(Throwable $exception, $displayErrorDetails): string
    {
        $renderer = new HtmlErrorRenderer();
        return $renderer($exception, $displayErrorDetails);
    }
}
