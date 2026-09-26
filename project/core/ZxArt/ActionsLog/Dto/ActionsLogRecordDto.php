<?php

declare(strict_types=1);

namespace ZxArt\ActionsLog\Dto;

/**
 * One audited action: who did what to which element and when.
 */
final readonly class ActionsLogRecordDto
{
    public function __construct(
        public int $elementId,
        public string $elementType,
        public string $elementName,
        public string $action,
        public int $userId,
        public string $userName,
        public string $userIp,
        public int $date,
    ) {
    }
}
