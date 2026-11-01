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
        if ($row === null) {
            return null;
        }

        return new ConversationDto(
            id: (int)$row['id'],
            userId: (int)$row['userId'],
            title: $row['title'],
            createdAt: (int)$row['createdAt'],
            lastMessageAt: (int)$row['lastMessageAt'],
        );
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
}
