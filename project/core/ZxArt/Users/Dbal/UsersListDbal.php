<?php

declare(strict_types=1);

namespace ZxArt\Users\Dbal;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use ZxArt\LinkTypes;
use ZxArt\Shared\Dbal\AbstractDbal;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\StructureType;
use ZxArt\Users\Dto\UsersListQueryDto;
use ZxArt\Users\UsersListSort;
use ZxArt\Users\UserStatusFilter;

/**
 * The users list of the management screen, searched, filtered, sorted and
 * paged in the database.
 *
 * @psalm-type UserListRow = array{id: int, userName: string, email: string, dateCreated: int, verified: bool, banned: bool}
 */
final readonly class UsersListDbal extends AbstractDbal
{
    public function __construct(
        private Connection $db,
    ) {
    }

    /**
     * @return list<UserListRow>
     */
    public function find(UsersListQueryDto $query): array
    {
        $users = $this->tableName(DatabaseTable::User);
        $elements = $this->tableName(DatabaseTable::StructureElements);
        $sortColumn = match ($query->sort) {
            UsersListSort::DateCreated => $elements . '.dateCreated',
            UsersListSort::UserName => $users . '.userName',
            UsersListSort::Email => $users . '.email',
        };

        /** @var list<array{id: int|string, userName: string, email: string, dateCreated: int|string, verified: int|string, banned: int|string}> $rows */
        $rows = $this->filtered($query)
            ->orderBy($sortColumn, $query->direction->value)
            ->orderBy($users . '.id', $query->direction->value)
            ->offset(($query->page - 1) * $query->pageSize)
            ->limit($query->pageSize)
            ->get([
                $users . '.id',
                $users . '.userName',
                $users . '.email',
                $elements . '.dateCreated',
                $users . '.verified',
                $users . '.banned',
            ]);

        return array_map(static fn(array $row): array => [
            'id' => (int)$row['id'],
            'userName' => $row['userName'],
            'email' => $row['email'],
            'dateCreated' => (int)$row['dateCreated'],
            'verified' => (int)$row['verified'] === 1,
            'banned' => (int)$row['banned'] === 1,
        ], $rows);
    }

    public function count(UsersListQueryDto $query): int
    {
        return $this->filtered($query)->count();
    }

    private function filtered(UsersListQueryDto $query): Builder
    {
        $users = $this->tableName(DatabaseTable::User);
        $elements = $this->tableName(DatabaseTable::StructureElements);
        $links = $this->tableName(DatabaseTable::StructureLinks);

        $builder = $this->db->table($users)
            ->join($elements, $elements . '.id', '=', $users . '.id')
            ->where($elements . '.structureType', '=', StructureType::User->value);

        $search = trim($query->search);
        if ($search !== '') {
            $pattern = '%' . addcslashes($search, '\\%_') . '%';
            $builder->where(static function (Builder $condition) use ($users, $pattern): void {
                $condition->where($users . '.userName', 'like', $pattern)
                    ->orWhere($users . '.email', 'like', $pattern);
            });
        }
        if ($query->groupId > 0) {
            $builder->whereIn($users . '.id', function (Builder $members) use ($links, $query): void {
                $members->from($links)
                    ->select('childStructureId')
                    ->where('parentStructureId', '=', $query->groupId)
                    ->where('type', '=', LinkTypes::USER_RELATION->value);
            });
        }
        if ($query->status === UserStatusFilter::Banned) {
            $builder->where($users . '.banned', '=', 1);
        } elseif ($query->status === UserStatusFilter::Active) {
            $builder->where($users . '.banned', '=', 0);
        }

        return $builder;
    }
}
