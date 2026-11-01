<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Chat\Dbal;

use Illuminate\Database\Connection;
use JsonException;
use ZxArt\Assistant\Chat\Dto\PlanDto;
use ZxArt\Assistant\Chat\PlanStatus;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\Dbal\AbstractDbal;

/**
 * @psalm-type PlanRow = array{id: int|string, conversationId: int|string, title: string, status: string, actions: string, createdAt: int|string, approvedAt: int|string|null, finishedAt: int|string|null}
 */
final readonly class PlansDbal extends AbstractDbal
{
    private const array COLUMNS = ['id', 'conversationId', 'title', 'status', 'actions', 'createdAt', 'approvedAt', 'finishedAt'];

    public function __construct(
        private Connection $db,
    ) {
    }

    /**
     * @param list<array<array-key, mixed>> $actions
     * @throws JsonException
     */
    public function create(int $conversationId, string $title, array $actions, int $now): int
    {
        return (int)$this->db->table($this->tableName(DatabaseTable::AiPlan))->insertGetId([
            'conversationId' => $conversationId,
            'title' => $title,
            'status' => PlanStatus::Proposed->value,
            'actions' => $this->encode($actions),
            'createdAt' => $now,
        ]);
    }

    /**
     * @throws JsonException
     */
    public function find(int $id): ?PlanDto
    {
        /** @var PlanRow|null $row */
        $row = $this->db->table($this->tableName(DatabaseTable::AiPlan))
            ->where('id', '=', $id)
            ->first(self::COLUMNS);
        if ($row === null) {
            return null;
        }
        /** @var list<array<array-key, mixed>> $actions */
        $actions = json_decode($row['actions'], true, 512, JSON_THROW_ON_ERROR);

        return new PlanDto(
            id: (int)$row['id'],
            conversationId: (int)$row['conversationId'],
            title: $row['title'],
            status: PlanStatus::from($row['status']),
            actions: $actions,
            createdAt: (int)$row['createdAt'],
            approvedAt: $row['approvedAt'] === null ? null : (int)$row['approvedAt'],
            finishedAt: $row['finishedAt'] === null ? null : (int)$row['finishedAt'],
        );
    }

    /**
     * @return list<PlanDto> oldest first
     * @throws JsonException
     */
    public function findByConversation(int $conversationId): array
    {
        /** @var list<int|string> $ids */
        $ids = $this->db->table($this->tableName(DatabaseTable::AiPlan))
            ->where('conversationId', '=', $conversationId)
            ->orderBy('id')
            ->pluck('id');
        $plans = [];
        foreach ($ids as $id) {
            $plan = $this->find((int)$id);
            if ($plan !== null) {
                $plans[] = $plan;
            }
        }

        return $plans;
    }

    public function approve(int $id, int $now): void
    {
        $this->db->table($this->tableName(DatabaseTable::AiPlan))
            ->where('id', '=', $id)
            ->update(['status' => PlanStatus::Executing->value, 'approvedAt' => $now]);
    }

    /**
     * @param list<array<array-key, mixed>> $actions
     * @throws JsonException
     */
    public function saveActions(int $id, array $actions): void
    {
        $this->db->table($this->tableName(DatabaseTable::AiPlan))
            ->where('id', '=', $id)
            ->update(['actions' => $this->encode($actions)]);
    }

    public function finish(int $id, PlanStatus $status, int $now): void
    {
        $this->db->table($this->tableName(DatabaseTable::AiPlan))
            ->where('id', '=', $id)
            ->update(['status' => $status->value, 'finishedAt' => $now]);
    }

    public function supersedeProposed(int $conversationId): void
    {
        $this->db->table($this->tableName(DatabaseTable::AiPlan))
            ->where('conversationId', '=', $conversationId)
            ->where('status', '=', PlanStatus::Proposed->value)
            ->update(['status' => PlanStatus::Superseded->value]);
    }

    /**
     * @param list<array<array-key, mixed>> $actions
     * @throws JsonException
     */
    private function encode(array $actions): string
    {
        return json_encode($actions, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }
}
