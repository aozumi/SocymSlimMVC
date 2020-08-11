<?php
namespace SocymSlim\MVC\controllers;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Container\ContainerInterface;
use Slim\Views\Twig;
use PDO;
use PDOException;

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

    function db(): PDO
    {
        return $this->container->get("db");
    }

    public function goMemberAdd(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $response = $this->twig()->render($response, "memberAdd.html");
        return $response;
    }

    public function memberAdd(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $postParams = $request->getParsedBody();
        $addMbNameLast = $postParams['addMbNameLast'];
        $addMbNameFirst = $postParams['addMbNameFirst'];
        $addMbBirth = $postParams['addMbBirth'];
        $addMbType = $postParams['addMbType'];

        $addMbNameLast = \trim($addMbNameLast);
        $addMbNameFirst = \trim($addMbNameFirst);

        global $dbUrl, $dbUsername, $dbPassword;

        // 登録用SQL
        $sqlInsert = "INSERT INTO members (mb_name_last, mb_name_first, mb_birth, mb_type) VALUES (:mb_name_last, :mb_name_first, :mb_birth, :mb_type)";

        try {
            $db = $this->db();

            // プリペアードステートメントのインスタンスを取得して、変数を束縛
            $stmt = $db->prepare($sqlInsert);
            $stmt->bindValue(':mb_name_last', $addMbNameLast, PDO::PARAM_STR);
            $stmt->bindValue(':mb_name_first', $addMbNameFirst, PDO::PARAM_STR);
            $stmt->bindValue(':mb_birth', $addMbBirth, PDO::PARAM_STR);
            $stmt->bindValue(':mb_type', $addMbType, PDO::PARAM_INT);

            // SQL実行
            $result = $stmt->execute();

            if ($result) {
                // SQL成功
                $mbId = $db->lastInsertId();
                $content = 'ID ' . $mbId . 'で登録が完了しました。';
            } else {
                $content = '登録に失敗しました。';
            }
        } catch (PDOException $ex) {
            $content = '障害が発生しました。'; 
            var_dump($ex);
        } finally {
            $db = null;  // データベース接続を切断
        }

        $response->getBody()->write($content);
        return $response;
    }

    public function showMemberDetail(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $memberId = $args['id'];
        $sqlSelect = 'SELECT * FROM members WHERE id = :id';

        try {
            $db = $this->db();
            $stmt = $db->prepare($sqlSelect);
            $stmt->bindValue(':id', $memberId, PDO::PARAM_INT);
            $result = $stmt->execute();
            if ($result) { // SELECT成功
                if ($row = $stmt->fetch()) {
                    $id = $row['id'];
                    $mbNameLast = $row['mb_name_last'];
                    $mbNameFirst = $row['mb_name_first'];
                    $mbBirth = $row['mb_birth'];
                    $mbType = $row['mb_type'];

                    $content = 'ID: ' . $id . '<br>氏名: ' . \htmlspecialchars($mbNameLast) . \htmlspecialchars($mbNameFirst) . 
                        '<br>生年月日: ' . $mbBirth . '<br>会員種別: ' . $mbType;
                } else {
                    $content = '指定された会員情報は存在しません';
                }
            } else {
                $content = 'データ取得に失敗しました';
            }
        } catch (PDOException $ex) {
            $content = '障害が発生しました。';
            var_dump($ex);
        } finally {
            $db = null; // DB切断
        }

        $response->getBody()->write($content);
        return $response;
    }
}

