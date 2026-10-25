<?php

declare(strict_types=1);

namespace ZxArt\Prods\Services;

use structureManager;
use ZxArt\Authors\Dbal\AuthorshipDbal;
use ZxArt\Import\Services\ImportOriginsReader;
use ZxArt\Prods\Dto\ProdStateDto;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Prods\ProdFileKind;
use ZxArt\Releases\Dbal\ReleasesDbal;
use ZxArt\Shared\Dto\AttachedFileDto;
use ZxArt\Shared\Elements\AttachedFilesReader;
use ZxArt\Shared\EntityType;
use zxProdElement;

/**
 * Reads the complete state of a production. Checks no privilege beyond what
 * loading the element checks.
 *
 * @psalm-api
 */
final readonly class ProdStateService
{
    public function __construct(
        private structureManager $structureManager,
        private ProdFieldsReader $prodFieldsReader,
        private AuthorshipDbal $authorshipDbal,
        private ReleasesDbal $releasesDbal,
        private AttachedFilesReader $attachedFilesReader,
        private ImportOriginsReader $importOriginsReader,
    ) {
    }

    public function get(int $prodId): ProdStateDto
    {
        $prod = $this->structureManager->getElementById($prodId);
        if (!$prod instanceof zxProdElement) {
            throw new ProdDataException('Production not found', 404);
        }

        return new ProdStateDto(
            id: $prodId,
            modifiedAt: $prod->getModifiedTimestamp(),
            fields: $this->prodFieldsReader->read($prod),
            authors: $this->authorshipDbal->findByElement($prodId, EntityType::Prod),
            releaseIds: $this->releasesDbal->findIdsByProdId($prodId),
            files: $this->readFiles($prod),
            importOrigins: $this->importOriginsReader->read($prodId),
        );
    }

    /**
     * @return array<string, AttachedFileDto[]>
     */
    private function readFiles(zxProdElement $prod): array
    {
        $files = [];
        foreach (ProdFileKind::cases() as $kind) {
            $files[$kind->value] = $this->attachedFilesReader->read($prod, $kind);
        }

        return $files;
    }
}
