<?php

declare(strict_types=1);

namespace ZxArt\ActionsLog;

use App\Users\CurrentUserService;
use structureElement;
use ZxArt\ActionsLog\Dto\ActionsLogRecordDto;
use ZxArt\ActionsLog\Repositories\ActionsLogRepository;
use ZxArt\Shared\StructureType;

/**
 * Audit trail of content changes: every create, update and delete records the
 * acting user in the actions log, under the action's privilege name, the way
 * loggable legacy element actions do.
 */
final readonly class ActionsLogService
{
    public function __construct(
        private ActionsLogRepository $actionsLogRepository,
        private CurrentUserService $currentUserService,
    ) {
    }

    public function log(structureElement $element, StructureType $structureType, string $action): void
    {
        $user = $this->currentUserService->getCurrentUser();
        $this->actionsLogRepository->add(new ActionsLogRecordDto(
            elementId: $element->getId(),
            elementType: $structureType->value,
            elementName: $element->getStructureName(),
            action: $action,
            userId: (int)$user->id,
            userName: $user->userName,
            userIp: (string)$user->IP,
            date: time(),
        ));
    }
}
