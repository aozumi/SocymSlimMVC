<?php
namespace SocymSlim\MVC\controllers;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Container\ContainerInterface;
use Slim\Views\Twig;
use PDO;
use PDOException;

use SocymSlim\MVC\entities\Member;
use SocymSlim\MVC\daos\MemberDAO;

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

    private function rowToMember($row): Member {
        $member = new Member();
        $member->setId($row['id']);
        $member->setMbNameLast($row['mb_name_last']);
        $member->setMbNameFirst($row['mb_name_first']);
        $member->setMbBirth($row['mb_birth']);
        $member->setMbType($row['mb_type']);
        return $member;
    }

    public function showMemberDetail(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $templateParams = [];  // テンプレートに渡すパラメータ
        $memberId = $args['id'];

        try {
            $db = $this->db();
            $dao = new MemberDAO($db);
            $member = $dao->findByPK($memberId);
            if (isset($member)) {
                $templateParams['memberInfo'] = $member;
            } else {
                $templateParams['msg'] = '指定された会員情報は存在しません';
            }
        } catch (PDOException $ex) {
            $templateParams['msg'] = '障害が発生しました。';
            var_dump($ex);
        } finally {
            $db = null; // DB切断
            $dao = null;
        }

        $response = $this->twig()->render($response, 'memberDetail.html', $templateParams);
        return $response;
    }

    public function getAllMembersJSON(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $sqlSelect = 'SELECT * FROM members';

        try {
            $db = $this->db();
            $stmt = $db->prepare($sqlSelect);
            $result = $stmt->execute();
            if ($result) {
                $jsonArray = [
                    'members' => $stmt->fetchAll(),
                    'msg' => 'データ取得に成功しました'
                ];
            } else {
                $jsonArray = ['msg' => 'データ取得に失敗しました'];
            }
        } catch (PDOException $ex) {
            $jsonArray = ['msg' => '障害が発生しました'];
            var_dump($ex);
        } finally {
            $db = null;
        }

        $response->getBody()->write(\json_encode($jsonArray));
        $response = $response->withHeader('Content-Type', 'application/json');
        return $response;
    }

    public function showMembersList(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $templateParams = [];
        $membersList = [];

        try {
            $db = $this->db();
            $dao = new MemberDAO($db);
            $membersList = $dao->findAll();
        } catch (PDOException $ex) {
            $templateParams['msg'] = '障害が発生しました';
        } finally {
            $db = null;
        }
        $templateParams['membersList'] = $membersList;

        $response = $this->twig()->render($response, 'membersList.html', $templateParams);
        return $response;
    }
}

