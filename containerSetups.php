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
        // PDOオブジェクトの生成 = データベース接続
        if (isset($_ENV['DATABASE_URL'])) {
            $db = makePdoByEnv($_ENV['DATABASE_URL']);
        } else {
            global $dbUrl, $dbUsername, $dbPassword;
            $db = new PDO($dbUrl, $dbUsername, $dbPassword);
        }
        // PDOのエラー表示モードを例外モードに
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // prepared statementを有効に
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        // フェッチモードを各行をカラム名の連想配列として返すよう設定
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        return $db;
    });

AppFactory::setContainer($container);

function makePdoByEnv(string $uri): PDO
{
    $parsed = parse_url($uri);

    $host = $parsed['host'];
    $port = $parsed['port'];
    $user = $parsed['user'];
    $password = $parsed['pass'];
    $dbname = ltrim($parsed['path'], '/');

    $dns = "pgsql:dbname=" . $dbname . ";host=" . $host . ";port=" . $port;
    return new PDO($dns, $user, $password);
}
