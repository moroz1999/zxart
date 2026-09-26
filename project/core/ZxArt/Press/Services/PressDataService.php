<?php

declare(strict_types=1);

namespace ZxArt\Press\Services;

use pressArticleElement;
use privilegesManager;
use structureManager;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Press\Dto\PressDataResultDto;
use ZxArt\Press\Exception\PressDataException;
use ZxArt\Shared\StructureType;

/**
 * Changes to a press article requested through `/press-data/`. Each change checks the
 * privilege of the matching legacy action and is recorded in the actions log.
 */
final readonly class PressDataService
{
    private const string DELETE_PRIVILEGE = 'publicDelete';

    public function __construct(
        private structureManager $structureManager,
        private privilegesManager $privilegesManager,
        private ActionsLogService $actionsLogService,
    ) {
    }

    public function delete(int $articleId): PressDataResultDto
    {
        $article = $this->getArticle($articleId, self::DELETE_PRIVILEGE);
        // logged first: the element data is gone after the deletion
        $this->actionsLogService->log($article, StructureType::PressArticle, self::DELETE_PRIVILEGE);
        $article->deleteElementData();

        return new PressDataResultDto(id: $articleId);
    }

    private function getArticle(int $articleId, string $privilege): pressArticleElement
    {
        if ($articleId <= 0) {
            throw new PressDataException('Missing required press article id', 400);
        }
        $article = $this->structureManager->getElementById($articleId);
        if (!$article instanceof pressArticleElement) {
            throw new PressDataException('Press article not found', 404);
        }
        $isAllowed = $this->privilegesManager->checkPrivilegesForAction(
            $articleId,
            $privilege,
            StructureType::PressArticle->value,
        ) === true;
        if (!$isAllowed) {
            throw new PressDataException('This press article change is forbidden', 403);
        }

        return $article;
    }
}
