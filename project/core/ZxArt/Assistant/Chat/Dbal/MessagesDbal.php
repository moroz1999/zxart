<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Chat\Dbal;

use Illuminate\Database\Connection;
use JsonException;
use ZxArt\Assistant\Chat\Dto\MessageDto;
use ZxArt\Assistant\Chat\MessageKind;
use ZxArt\Assistant\Chat\MessageRole;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\Dbal\AbstractDbal;

/**
 * @psalm-type MessageRow = array{id: int|string, conversationId: int|string, seq: int|string, role: string, kind: string, text: string, payload: string|null, createdAt: int|string}
 */
final readonly class MessagesDbal extends AbstractDbal
{
    private const array COLUMNS = ['id', 'conversationId', 'seq', 'role', 'kind', 'text', 'payload', 'createdAt'];

    public function __construct(
        private Connection $db,
    ) {
    }

    /**
     * Stores the message under the conversation's next `seq`.
     *
     * @param array<array-key, mixed>|null $payload
     * @throws JsonException
     */
    public function append(int $conversationId, MessageRole $role, MessageKind $kind, string $text, ?array $payload, int $now): MessageDto
    {
        $seq = (int)$this->db->table($this->tableName(DatabaseTable::AiMessage))
            ->where('conversationId', '=', $conversationId)
            ->max('seq') + 1;
        $id = (int)$this->db->table($this->tableName(DatabaseTable::AiMessage))->insertGetId([
            'conversationId' => $conversationId,
            'seq' => $seq,
            'role' => $role->value,
            'kind' => $kind->value,
            'text' => $text,
            'payload' => $payload === null ? null : json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            'createdAt' => $now,
        ]);

        return new MessageDto($id, $conversationId, $seq, $role, $kind, $text, $payload, $now);
    }

    /**
     * @return list<MessageDto> in `seq` order
     * @throws JsonException
     */
    public function findAfter(int $conversationId, int $afterSeq): array
    {
        /** @var list<MessageRow> $rows */
        $rows = $this->db->table($this->tableName(DatabaseTable::AiMessage))
            ->where('conversationId', '=', $conversationId)
            ->where('seq', '>', $afterSeq)
            ->orderBy('seq')
            ->get(self::COLUMNS);

        return array_map($this->toDto(...), $rows);
    }

    /**
     * @param MessageRow $row
     * @throws JsonException
     */
    private function toDto(array $row): MessageDto
    {
        /** @var array<array-key, mixed>|null $payload */
        $payload = $row['payload'] === null ? null : json_decode($row['payload'], true, 512, JSON_THROW_ON_ERROR);

        return new MessageDto(
            id: (int)$row['id'],
            conversationId: (int)$row['conversationId'],
            seq: (int)$row['seq'],
            role: MessageRole::from($row['role']),
            kind: MessageKind::from($row['kind']),
            text: $row['text'],
            payload: $payload,
            createdAt: (int)$row['createdAt'],
        );
    }
}
