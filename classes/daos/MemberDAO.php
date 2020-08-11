<?php
namespace SocymSlim\MVC\daos;

use PDO;
use SocymSlim\MVC\entities\Member;

class MemberDAO
{
    private $db;  // PDOインスタンス

    public function __construct(PDO $db)
    {
        $this->db = $db;
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

    // 主キーによる検索
    public function findByPK(int $id): ?Member
    {
        $sqlSelect = 'SELECT * FROM members WHERE id = :id';
        $stmt = $this->db->prepare($sqlSelect);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $result = $stmt->execute();
        if ($result && $row = $stmt->fetch()) {
            $member = $this->rowToMember($row);
            return $member;
        } else {
            return null;
        }
    }

    public function findAll(): array
    {
        $sqlSelect = 'SELECT * FROM members ORDER BY id';
        $membersList = [];

        $stmt = $this->db->prepare($sqlSelect);
        $result = $stmt->execute();

        if ($result) {
            while ($row = $stmt->fetch()) {
                $member = $this->rowToMember($row);
                $membersList[$member->getId()] = $member;
            }
        }
        return $membersList; 
    }
}
