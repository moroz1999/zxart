<?php

declare(strict_types=1);

namespace ZxArt\Prods;

use linksManager;
use persistableObject;
use privilegesManager;
use structureManager;
use ZxArt\LinkTypes;
use ZxArt\Prods\Dto\ProdFilesDto;
use ZxArt\Prods\Exception\ProdDetailsException;
use ZxArt\Shared\StructureType;

final readonly class ProdScreenshotMoveService
{
    public function __construct(
        private ProdElementService $prodElementService,
        private ProdMediaService $prodMediaService,
        private linksManager $linksManager,
        private privilegesManager $privilegesManager,
        private structureManager $structureManager,
    ) {
    }

    public function move(int $elementId, int $fileId, string $direction): ProdFilesDto
    {
        $this->prodElementService->get($elementId);

        $hasPrivilege = $this->privilegesManager->checkPrivilegesForAction(
            $elementId,
            'publicReceive',
            StructureType::ZxProd->value,
        );
        if ($hasPrivilege !== true) {
            throw new ProdDetailsException('Access denied', 403);
        }

        $links = array_values(
            $this->linksManager->getElementsLinks($elementId, LinkTypes::CONNECTED_FILE->value, 'parent')
        );

        $currentLink = $this->findLink($links, $fileId);
        if ($currentLink === null) {
            throw new ProdDetailsException('File not found', 404);
        }

        usort($links, static fn(persistableObject $a, persistableObject $b) => (int)$a->position <=> (int)$b->position);

        $swapLink = $this->findSwapLink($links, $currentLink, $direction);
        if ($swapLink !== null) {
            $this->swapPositions($currentLink, $swapLink);
            $this->linksManager->resetElementsCacheById($elementId);
            $this->structureManager->clearElementCache($elementId);
        }

        return $this->prodMediaService->getProdScreenshots($elementId);
    }

    /**
     * @param persistableObject[] $links
     */
    private function findLink(array $links, int $fileId): ?persistableObject
    {
        foreach ($links as $link) {
            if ((int)$link->childStructureId === $fileId) {
                return $link;
            }
        }
        return null;
    }

    /**
     * @param persistableObject[] $links
     */
    private function findSwapLink(array $links, persistableObject $currentLink, string $direction): ?persistableObject
    {
        $count = count($links);
        for ($i = 0; $i < $count; $i++) {
            if ((string)$links[$i]->id !== (string)$currentLink->id) {
                continue;
            }
            if ($direction === 'right' && $i < $count - 1) {
                return $links[$i + 1];
            }
            if ($direction === 'left' && $i > 0) {
                return $links[$i - 1];
            }
            break;
        }
        return null;
    }

    private function swapPositions(persistableObject $a, persistableObject $b): void
    {
        $positionA = $a->position;
        $a->position = $b->position;
        $b->position = $positionA;
        $a->persist();
        $b->persist();
    }
}
