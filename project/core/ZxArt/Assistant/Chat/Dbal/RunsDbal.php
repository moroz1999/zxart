<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Chat\Dbal;

use Illuminate\Database\Connection;
use ZxArt\Assistant\Chat\Dto\RunDto;
use ZxArt\Assistant\Chat\RunStatus;
use ZxArt\Assistant\Chat\RunType;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\Dbal\AbstractDbal;

/**
 * @psalm-type RunRow = array{id: int|string, conversationId: int|string, type: string, status: string, steps: int|string, tokensIn: int|string, tokensOut: int|string, error: string|null, startedAt: int|string, finishedAt: int|string|null}
 */
final readonly class RunsDbal extends AbstractDbal
{
    private const array COLUMNS = ['id', 'conversationId', 'type', 'status', 'steps', 'tokensIn', 'tokensOut', 'error', 'startedAt', 'finishedAt'];

    public function __construct(
        private Connection $db,
    ) {
    }

    public function start(int $conversationId, RunType $type, int $now): int
    {
        return (int)$this->db->table($this->tableName(DatabaseTable::AiRun))->insertGetId([
            'conversationId' => $conversationId,
            'type' => $type->value,
            'status' => RunStatus::Running->value,
            'startedAt' => $now,
        ]);
    }

    public function find(int $id): ?RunDto
    {
        /** @var RunRow|null $row */
        $row = $this->db->table($this->tableName(DatabaseTable::AiRun))
            ->where('id', '=', $id)
            ->first(self::COLUMNS);

        return $row === null ? null : $this->toDto($row);
    }

    public function findActive(int $conversationId): ?RunDto
    {
        /** @var RunRow|null $row */
        $row = $this->db->table($this->tableName(DatabaseTable::AiRun))
            ->where('conversationId', '=', $conversationId)
            ->where('status', '=', RunStatus::Running->value)
            ->orderBy('id', 'desc')
            ->first(self::COLUMNS);

        return $row === null ? null : $this->toDto($row);
    }

    public function recordStep(int $id, int $tokensIn, int $tokensOut): void
    {
        $this->db->table($this->tableName(DatabaseTable::AiRun))
            ->where('id', '=', $id)
            ->update([
                'steps' => $this->db->raw('steps + 1'),
                'tokensIn' => $this->db->raw('tokensIn + ' . $tokensIn),
                'tokensOut' => $this->db->raw('tokensOut + ' . $tokensOut),
            ]);
    }

    public function finish(int $id, RunStatus $status, ?string $error, int $now): void
    {
        $this->db->table($this->tableName(DatabaseTable::AiRun))
            ->where('id', '=', $id)
            ->update(['status' => $status->value, 'error' => $error, 'finishedAt' => $now]);
    }

    /**
     * @param RunRow $row
     */
    private function toDto(array $row): RunDto
    {
        return new RunDto(
            id: (int)$row['id'],
            conversationId: (int)$row['conversationId'],
            type: RunType::from($row['type']),
            status: RunStatus::from($row['status']),
            steps: (int)$row['steps'],
            tokensIn: (int)$row['tokensIn'],
            tokensOut: (int)$row['tokensOut'],
            error: $row['error'],
            startedAt: (int)$row['startedAt'],
            finishedAt: $row['finishedAt'] === null ? null : (int)$row['finishedAt'],
        );
    }
}
