<?php

declare(strict_types=1);

namespace ZxArt\ActionsLog\Repositories;

use Illuminate\Database\Connection;
use ZxArt\ActionsLog\Dto\ActionsLogRecordDto;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\Repositories\AbstractRepository;

/**
 * @psalm-api
 */
final readonly class ActionsLogRepository extends AbstractRepository
{
    public function __construct(
        private Connection $db,
    ) {
    }

    public function add(ActionsLogRecordDto $record): void
    {
        $this->db->table($this->tableName(DatabaseTable::ActionsLog))->insert([
            'elementId' => $record->elementId,
            'elementType' => $record->elementType,
            'elementName' => $record->elementName,
            'action' => $record->action,
            'userId' => $record->userId,
            'userName' => $record->userName,
            'userIP' => $record->userIp,
            'date' => $record->date,
        ]);
    }
}
