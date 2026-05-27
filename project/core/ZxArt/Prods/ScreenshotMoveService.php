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
use zxProdElement;
use zxReleaseElement;

final readonly class ScreenshotMoveService
{
    public function __construct(
        private ProdMediaService $prodMediaService,
        private linksManager $linksManager,
        private privilegesManager $privilegesManager,
        private structureManager $structureManager,
    ) {
    }

    public function move(int $elementId, int $fileId, ScreenshotMoveDirection $direction): ProdFilesDto
    {
        $element = $this->structureManager->getElementById($elementId);
        if ($element instanceof zxProdElement) {
            $structureType = StructureType::ZxProd;
            $linkType = LinkTypes::CONNECTED_FILE;
        } elseif ($element instanceof zxReleaseElement) {
            $structureType = StructureType::ZxRelease;
            $linkType = LinkTypes::SCREENSHOTS_SELECTOR;
        } else {
            throw new ProdDetailsException('Element not found', 404);
        }

        $hasPrivilege = $this->privilegesManager->checkPrivilegesForAction(
            $elementId,
            'publicReceive',
            $structureType->value,
        );
        if ($hasPrivilege !== true) {
            throw new ProdDetailsException('Access denied', 403);
        }

        $links = array_values(
            $this->linksManager->getElementsLinks($elementId, $linkType->value, 'parent')
        );

        $currentLink = $this->findLink($links, $fileId);
        if ($currentLink === null) {
            throw new ProdDetailsException('File not found', 404);
        }

        usort($links, static fn(persistableObject $first, persistableObject $second) => (int)$first->position <=> (int)$second->position);

        $swapLink = $this->findSwapLink($links, $currentLink, $direction);
        if ($swapLink !== null) {
            $this->swapPositions($currentLink, $swapLink);
            $this->linksManager->resetElementsCacheById($elementId);
            $this->structureManager->clearElementCache($elementId);
        }

        return $element instanceof zxProdElement
            ? $this->prodMediaService->getProdScreenshots($elementId)
            : $this->prodMediaService->getReleaseScreenshots($elementId);
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
    private function findSwapLink(
        array $links,
        persistableObject $currentLink,
        ScreenshotMoveDirection $direction,
    ): ?persistableObject {
        $count = count($links);
        for ($index = 0; $index < $count; $index++) {
            if ((string)$links[$index]->id !== (string)$currentLink->id) {
                continue;
            }
            if ($direction === ScreenshotMoveDirection::Right && $index < $count - 1) {
                return $links[$index + 1];
            }
            if ($direction === ScreenshotMoveDirection::Left && $index > 0) {
                return $links[$index - 1];
            }
            break;
        }
        return null;
    }

    private function swapPositions(persistableObject $firstLink, persistableObject $secondLink): void
    {
        $firstPosition = $firstLink->position;
        $firstLink->position = $secondLink->position;
        $secondLink->position = $firstPosition;
        $firstLink->persist();
        $secondLink->persist();
    }
}
