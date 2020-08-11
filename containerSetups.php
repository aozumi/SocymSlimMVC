<?php
use DI\Container;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use PDO;

$container = new Container();

$container->set("view",
    function () {
        global $TOPDIR;
        $twig = Twig::create($TOPDIR . '/templates');
        return $twig;
    });

$container->set("db",
    function () {
        global $dbUrl, $dbUsername, $dbPassword;

        // PDOオブジェクトの生成 = データベース接続
        $db = new PDO($dbUrl, $dbUsername, $dbPassword);
        // PDOのエラー表示モードを例外モードに
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // prepared statementを有効に
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        // フェッチモードを各行をカラム名の連想配列として返すよう設定
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        return $db;
    });

AppFactory::setContainer($container);
