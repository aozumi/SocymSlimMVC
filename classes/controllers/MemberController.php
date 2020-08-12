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

        $member = new Member();
        $member->setMbNameLast($addMbNameLast);
        $member->setMbNameFirst($addMbNameFirst);
        $member->setMbBirth($addMbBirth);
        $member->setMbType($addMbType);

        $redirect = false;  // 応答でリダイレクトするかのフラグ

        try {
            $dao = new MemberDAO($this->db());
            $mbId = $dao->insert($member);
            if ($mbId != -1) {
                $content = 'ID ' . $mbId . 'で登録が完了しました。';
                $redirect = true;
            } else {
                $content = '登録に失敗しました。';
            }
        } catch (PDOException $ex) {
            $content = '障害が発生しました。'; 
            var_dump($ex);
        } finally {
            $dao = null;  // データベース接続を切断
        }

        if ($redirect) {
            return ($response->withHeader('Location', '/showMembersList')
                ->withStatus(302));
        } else {
            $response->getBody()->write($content);
            return $response;
        }
    }

    public function showMemberDetail(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $templateParams = [];  // テンプレートに渡すパラメータ
        $memberId = $args['id'];

        try {
            $dao = new MemberDAO($this->db());
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
            $dao = null;    // DB切断
        }

        $response = $this->twig()->render($response, 'memberDetail.html', $templateParams);
        return $response;
    }

    public function getAllMembersJSON(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $sqlSelect = 'SELECT * FROM members';

        try {
            $dao = new MemberDAO($this->db());
            $membersList = $dao->findAll2Array();
            if (! empty($membersList)) {
                $jsonArray = [
                    'members' => $membersList,
                    'msg' => 'データ取得に成功しました'
                ];
            } else {
                $jsonArray = ['msg' => 'データ取得に失敗しました'];
            }
        } catch (PDOException $ex) {
            $jsonArray = ['msg' => '障害が発生しました'];
            var_dump($ex);
        } finally {
            $dao = null;
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
            $dao = new MemberDAO($this->db());
            $membersList = $dao->findAll();
        } catch (PDOException $ex) {
            $templateParams['msg'] = '障害が発生しました';
        } finally {
            $dao = null;
        }
        $templateParams['membersList'] = $membersList;

        $response = $this->twig()->render($response, 'membersList.html', $templateParams);
        return $response;
    }
}

