<?php
use DI\Container;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;

$container = new Container();

$container->set("view",
    function() {
        global $TOPDIR;
        $twig = Twig::create($TOPDIR . '/templates');
        return $twig;
    });

AppFactory::setContainer($container);
