<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Users;

require_once __DIR__ . '/../Entities/EntityWriteTestCase.php';

use ZxArt\Tests\Integration\Entities\EntityWriteTestCase;

/**
 * User management checked against the local database: users and groups the
 * test writes itself, inside the transaction the base class rolls back.
 */
abstract class UserManagementTestCase extends EntityWriteTestCase
{
    protected const int USER_MANAGERS = 479;
    protected const int USERS_FOLDER = 13;
    protected const int GROUPS_FOLDER = 476;

    /**
     * A user stored the way registration stores one, in the users folder.
     */
    protected function createUser(string $userName, string $email, int $dateCreated, bool $banned = false, bool $verified = true): int
    {
        $userId = (int)$this->db->table('structure_elements')->insertGetId([
            'structureType' => 'user',
            'structureName' => $userName,
            'structureRole' => 'content',
            'dateCreated' => $dateCreated,
            'dateModified' => $dateCreated,
            'marker' => '',
        ]);
        $this->db->table('module_user')->insert([
            'id' => $userId,
            'languageId' => 0,
            'userName' => $userName,
            'password' => '',
            'email' => $email,
            'type' => 0,
            'palette' => '',
            'showemail' => 0,
            'subscribe' => 0,
            'verified' => $verified ? 1 : 0,
            'comments' => 1,
            'banned' => $banned ? 1 : 0,
            'authorId' => 0,
            'supporter' => 0,
            'vip' => 0,
            'volunteer' => 0,
        ]);
        $this->db->table('structure_links')->insert([
            'parentStructureId' => self::USERS_FOLDER,
            'childStructureId' => $userId,
            'type' => 'structure',
        ]);

        return $userId;
    }

    protected function createGroup(string $groupName, string $description = ''): int
    {
        $groupId = (int)$this->db->table('structure_elements')->insertGetId([
            'structureType' => 'userGroup',
            'structureName' => $groupName,
            'structureRole' => 'content',
            'dateCreated' => time(),
            'dateModified' => time(),
            'marker' => '',
        ]);
        $this->db->table('module_user_group')->insert([
            'id' => $groupId,
            'languageId' => 0,
            'groupName' => $groupName,
            'description' => $description,
        ]);
        $this->db->table('structure_links')->insert([
            'parentStructureId' => self::GROUPS_FOLDER,
            'childStructureId' => $groupId,
            'type' => 'structure',
        ]);

        return $groupId;
    }

    protected function addToGroup(int $userId, int $groupId): void
    {
        $this->db->table('structure_links')->insert([
            'parentStructureId' => $groupId,
            'childStructureId' => $userId,
            'type' => 'userRelation',
        ]);
    }

    /**
     * @return list<int> the groups the user belongs to, sorted
     */
    protected function groupsOf(int $userId): array
    {
        return $this->linkedParents($userId, 'userRelation');
    }

    /**
     * Signs in as a user manager holding the given actions on the public root.
     *
     * @param list<array{string, string}> $privilegesOnRoot structure type, action
     */
    protected function signInAsManager(array $privilegesOnRoot): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], $privilegesOnRoot);
    }
}
