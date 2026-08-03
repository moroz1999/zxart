<?php

declare(strict_types=1);

namespace ZxArt\Screenshots;

use linksManager;
use structureManager;
use ZxArt\Prods\Dto\ProdFilesDto;
use ZxArt\Prods\ProdMediaService;
use ZxArt\Screenshots\Exception\ScreenshotUploadException;
use zxProdElement;
use zxReleaseElement;

/**
 * Stores a raw screen dump sent as the request body. The target entity is
 * resolved by its id and runs its own `uploadScreenshot` action, which reads the
 * body and creates the connected file; the action also enforces privileges.
 */
final readonly class ScreenshotUploadService
{
    private const string ACTION_NAME = 'uploadScreenshot';

    public function __construct(
        private structureManager $structureManager,
        private linksManager $linksManager,
        private ProdMediaService $prodMediaService,
    ) {
    }

    public function upload(int $elementId): ProdFilesDto
    {
        $element = $this->structureManager->getElementById($elementId);
        $isProd = $element instanceof zxProdElement;
        if (!$isProd && !$element instanceof zxReleaseElement) {
            throw new ScreenshotUploadException('Element not found', 404);
        }

        $executed = $element->executeAction(self::ACTION_NAME);
        if ($executed !== true) {
            throw new ScreenshotUploadException('Access denied', 403);
        }

        $this->linksManager->resetElementsCacheById($elementId);
        $this->structureManager->clearElementCache($elementId);

        return $isProd
            ? $this->prodMediaService->getProdScreenshots($elementId)
            : $this->prodMediaService->getReleaseScreenshots($elementId);
    }
}
