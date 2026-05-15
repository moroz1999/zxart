<?php

declare(strict_types=1);

namespace ZxArt\Prods\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use ZxArt\LinkTypes;
use ZxArt\Prods\Dto\ProdTabsDto;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\Repositories\AbstractRepository;

readonly final class ProdTabsRepository extends AbstractRepository
{
    private const string IMPORT_ORIGIN_TABLE = 'import_origin';
    private const string MAPS_IMPORT_ORIGIN = 'maps';

    public function __construct(private Connection $db)
    {
    }

    public function buildTabs(int $prodId): ProdTabsDto
    {
        return new ProdTabsDto(
            hasReleases: $this->hasStructureLink($prodId, LinkTypes::STRUCTURE),
            hasScreenshots: $this->hasStructureLink($prodId, LinkTypes::CONNECTED_FILE),
            hasInlays: $this->hasInlayLinks($prodId),
            hasMaps: $this->hasMapFiles($prodId) || $this->hasSpeccyMapsUrl($prodId),
            hasRzx: $this->hasStructureLink($prodId, LinkTypes::RZX),
            hasPictures: $this->hasGameLinkOfType($prodId, DatabaseTable::ZxPicture),
            hasTunes: $this->hasGameLinkOfType($prodId, DatabaseTable::ZxMusic),
            hasArticles: $this->hasArticleLinks($prodId),
            hasSeries: $this->hasSymmetricLink($prodId, LinkTypes::SERIES),
            hasCompilations: $this->hasSymmetricLink($prodId, LinkTypes::COMPILATION),
        );
    }

    private function hasStructureLink(int $prodId, LinkTypes $linkType): bool
    {
        return $this->db->table($this->tableName(DatabaseTable::StructureLinks))
            ->where('parentStructureId', '=', $prodId)
            ->where('type', '=', $linkType->value)
            ->exists();
    }

    private function hasInlayLinks(int $prodId): bool
    {
        $releaseIdsQuery = $this->db->table($this->tableName(DatabaseTable::StructureLinks))
            ->where('parentStructureId', '=', $prodId)
            ->where('type', '=', LinkTypes::STRUCTURE->value)
            ->select('childStructureId');

        return $this->db->table($this->tableName(DatabaseTable::StructureLinks))
            ->whereIn('parentStructureId', $releaseIdsQuery)
            ->where('type', '=', LinkTypes::INLAY_FILES_SELECTOR->value)
            ->exists();
    }

    private function hasMapFiles(int $prodId): bool
    {
        return $this->hasStructureLink($prodId, LinkTypes::MAP_FILES_SELECTOR);
    }

    private function hasSpeccyMapsUrl(int $prodId): bool
    {
        return $this->db->table(self::IMPORT_ORIGIN_TABLE)
            ->where('elementId', '=', $prodId)
            ->where('importOrigin', '=', self::MAPS_IMPORT_ORIGIN)
            ->exists();
    }

    private function hasGameLinkOfType(int $prodId, DatabaseTable $entityTable): bool
    {
        return $this->db->table($this->tableName(DatabaseTable::StructureLinks))
            ->join(
                $this->tableName($entityTable),
                $this->tableColumn($entityTable, 'id'),
                '=',
                $this->tableColumn(DatabaseTable::StructureLinks, 'childStructureId'),
            )
            ->where($this->tableColumn(DatabaseTable::StructureLinks, 'parentStructureId'), '=', $prodId)
            ->where($this->tableColumn(DatabaseTable::StructureLinks, 'type'), '=', LinkTypes::GAME_LINK->value)
            ->exists();
    }

    private function hasArticleLinks(int $prodId): bool
    {
        return $this->db->table($this->tableName(DatabaseTable::StructureLinks))
            ->where('parentStructureId', '=', $prodId)
            ->whereIn('type', [LinkTypes::PROD_ARTICLE->value, LinkTypes::PRESS_SOFTWARE->value])
            ->exists();
    }

    private function hasSymmetricLink(int $prodId, LinkTypes $linkType): bool
    {
        return $this->db->table($this->tableName(DatabaseTable::StructureLinks))
            ->where('type', '=', $linkType->value)
            ->where(function (Builder $query) use ($prodId): void {
                $query->where('parentStructureId', '=', $prodId)
                    ->orWhere('childStructureId', '=', $prodId);
            })
            ->exists();
    }
}
