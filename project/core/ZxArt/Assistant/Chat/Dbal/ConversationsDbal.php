<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Chat\Dbal;

use Illuminate\Database\Connection;
use ZxArt\Assistant\Chat\Dto\ConversationDto;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\Dbal\AbstractDbal;

final readonly class ConversationsDbal extends AbstractDbal
{
    public function __construct(
        private Connection $db,
    ) {
    }

    public function create(int $userId, int $now): int
    {
        return (int)$this->db->table($this->tableName(DatabaseTable::AiConversation))->insertGetId([
            'userId' => $userId,
            'title' => '',
            'createdAt' => $now,
            'lastMessageAt' => $now,
        ]);
    }

    public function find(int $id): ?ConversationDto
    {
        /** @var array{id: int|string, userId: int|string, title: string, createdAt: int|string, lastMessageAt: int|string}|null $row */
        $row = $this->db->table($this->tableName(DatabaseTable::AiConversation))
            ->where('id', '=', $id)
            ->first(['id', 'userId', 'title', 'createdAt', 'lastMessageAt']);

        return $row === null ? null : $this->toDto($row);
    }

    /**
     * @return list<ConversationDto> latest activity first
     */
    public function findByUser(int $userId): array
    {
        /** @var list<array{id: int|string, userId: int|string, title: string, createdAt: int|string, lastMessageAt: int|string}> $rows */
        $rows = $this->db->table($this->tableName(DatabaseTable::AiConversation))
            ->where('userId', '=', $userId)
            ->orderBy('lastMessageAt', 'desc')
            ->orderBy('id', 'desc')
            ->get(['id', 'userId', 'title', 'createdAt', 'lastMessageAt']);

        return array_map($this->toDto(...), $rows);
    }

    /**
     * Its messages, runs and plans go with it.
     */
    public function delete(int $id): void
    {
        $this->db->table($this->tableName(DatabaseTable::AiConversation))
            ->where('id', '=', $id)
            ->delete();
    }

    public function touch(int $id, int $lastMessageAt): void
    {
        $this->db->table($this->tableName(DatabaseTable::AiConversation))
            ->where('id', '=', $id)
            ->update(['lastMessageAt' => $lastMessageAt]);
    }

    public function setTitle(int $id, string $title): void
    {
        $this->db->table($this->tableName(DatabaseTable::AiConversation))
            ->where('id', '=', $id)
            ->update(['title' => $title]);
    }

    /**
     * @param array{id: int|string, userId: int|string, title: string, createdAt: int|string, lastMessageAt: int|string} $row
     */
    private function toDto(array $row): ConversationDto
    {
        return new ConversationDto(
            id: (int)$row['id'],
            userId: (int)$row['userId'],
            title: $row['title'],
            createdAt: (int)$row['createdAt'],
            lastMessageAt: (int)$row['lastMessageAt'],
        );
    }
}
