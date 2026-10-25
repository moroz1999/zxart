<?php

namespace ZxArt\Prods\Services;

use ElementsManager;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use LanguagesManager;
use linksManager;
use privilegesManager;
use structureElement;
use structureManager;
use ZxArt\Authors\Dbal\AuthorshipRepository;
use ZxArt\Import\ImportOrigin;
use ZxArt\Import\Prods\Dto\ProdImportDTO;
use ZxArt\Import\Prods\ProdImportPolicy;
use ZxArt\Import\Services\ImportIdOperator;
use ZxArt\Prods\Dbal\ProdsRepository;
use ZxArt\Prods\UseCases\ImportProdCommand;
use ZxArt\Prods\UseCases\ImportProdHandler;
use ZxArt\Shared\EntityType;
use zxProdElement;

class ProdsService extends ElementsManager
{
    protected const string TABLE = ProdsRepository::TABLE;

    private bool $forceUpdateYear = false;
    private bool $forceUpdateYoutube = false;
    private bool $forceUpdateExternalLink = false;
    private bool $forceUpdateCategories = false;
    private bool $forceUpdateImages = false;
    private bool $forceUpdateTitles = false;
    private bool $matchProdsWithoutYear = false;
    private bool $forceUpdateAuthors = false;
    private bool $forceUpdateGroups = false;
    private bool $forceUpdatePublishers = false;
    private bool $updateExistingProds = false;
    private bool $updateExistingReleases = false;
    private bool $forceUpdateReleaseAuthors = false;
    private bool $forceUpdateReleasePublishers = false;
    private bool $forceUpdateReleaseType = false;
    private bool $forceUpdateReleaseFiles = false;
    private bool $addImages = false;
    private bool $resizeImages = false;

    protected array $columnRelations = [];
    protected array $releaseColumnRelations = [];

    public function setForceUpdateExternalLink(bool $forceUpdateExternalLink): void
    {
        $this->forceUpdateExternalLink = $forceUpdateExternalLink;
    }

    public function setMatchProdsWithoutYear(bool $matchProdsWithoutYear): void
    {
        $this->matchProdsWithoutYear = $matchProdsWithoutYear;
    }

    public function setForceUpdateReleaseFiles(bool $forceUpdateReleaseFiles): void
    {
        $this->forceUpdateReleaseFiles = $forceUpdateReleaseFiles;
    }

    public function setForceUpdateReleaseType(bool $forceUpdateReleaseType): void
    {
        $this->forceUpdateReleaseType = $forceUpdateReleaseType;
    }

    public function setForceUpdateReleaseAuthors(bool $forceUpdateReleaseAuthors): void
    {
        $this->forceUpdateReleaseAuthors = $forceUpdateReleaseAuthors;
    }

    public function setForceUpdateReleasePublishers(bool $forceUpdateReleasePublishers): void
    {
        $this->forceUpdateReleasePublishers = $forceUpdateReleasePublishers;
    }

    public function setUpdateExistingReleases(bool $updateExistingReleases): void
    {
        $this->updateExistingReleases = $updateExistingReleases;
    }

    public function setResizeImages(bool $resizeImages): void
    {
        $this->resizeImages = $resizeImages;
    }

    public function __construct(
        protected structureManager $structureManager,
        protected linksManager $linksManager,
        protected privilegesManager $privilegesManager,
        protected AuthorshipRepository $authorshipRepository,
        protected Connection $db,
        protected LanguagesManager $languagesManager,
        private readonly ImportIdOperator $importIdOperator,
        private readonly ImportProdHandler $importProdHandler,
    )
    {
        $this->columnRelations = [
            'title' => ['title' => true],
            'place' => ['if(partyplace,0,1), partyplace' => true],
            'date' => ['dateCreated' => true, 'id' => true],
            'year' => ['year' => true, 'dateAdded' => true, 'id' => true],
            'votes' => ['votes' => true, 'if(partyplace,0,1), partyplace' => false, 'title' => true],
        ];
        $this->releaseColumnRelations = [
            'title' => ['title' => true],
            'date' => ['dateCreated' => true, 'id' => true],
            'year' => ['year' => true, 'id' => true],
        ];
    }

    public function setForceUpdateImages(bool $forceUpdateImages): void
    {
        $this->forceUpdateImages = $forceUpdateImages;
    }

    public function setUpdateExistingProds(bool $updateExistingProds): void
    {
        $this->updateExistingProds = $updateExistingProds;
    }

    public function setForceUpdateYear(bool $forceUpdateYear): void
    {
        $this->forceUpdateYear = $forceUpdateYear;
    }

    public function setForceUpdateYoutube(bool $forceUpdateYoutube): void
    {
        $this->forceUpdateYoutube = $forceUpdateYoutube;
    }

    public function setForceUpdateGroups(bool $forceUpdateGroups): void
    {
        $this->forceUpdateGroups = $forceUpdateGroups;
    }

    public function setForceUpdatePublishers(bool $forceUpdatePublishers): void
    {
        $this->forceUpdatePublishers = $forceUpdatePublishers;
    }

    public function setForceUpdateAuthors(bool $forceUpdateAuthors): void
    {
        $this->forceUpdateAuthors = $forceUpdateAuthors;
    }

    public function setForceUpdateCategories(bool $forceUpdateCategories): void
    {
        $this->forceUpdateCategories = $forceUpdateCategories;
    }

    public function setAddImages(bool $addImages): void
    {
        $this->addImages = $addImages;
    }

    public function setForceUpdateTitles(bool $forceUpdateTitles): void
    {
        $this->forceUpdateTitles = $forceUpdateTitles;
    }

    public function importProdOld(array $prodInfo, ImportOrigin $origin): zxProdElement
    {
        return $this->importProd(ProdImportDTO::fromArray($prodInfo), $origin);
    }

    /**
     * Runs {@see ImportProdHandler} with the policy the setters made.
     */
    public function importProd(ProdImportDTO $dto, ImportOrigin $origin): zxProdElement
    {
        return $this->importProdHandler->handle(new ImportProdCommand($dto, $origin, $this->policy()));
    }

    private function policy(): ProdImportPolicy
    {
        return new ProdImportPolicy(
            updateExistingProds: $this->updateExistingProds,
            updateExistingReleases: $this->updateExistingReleases,
            matchProdsWithoutYear: $this->matchProdsWithoutYear,
            forceUpdateTitles: $this->forceUpdateTitles,
            forceUpdateYear: $this->forceUpdateYear,
            forceUpdateYoutube: $this->forceUpdateYoutube,
            forceUpdateExternalLink: $this->forceUpdateExternalLink,
            forceUpdateCategories: $this->forceUpdateCategories,
            forceUpdateAuthors: $this->forceUpdateAuthors,
            forceUpdateGroups: $this->forceUpdateGroups,
            forceUpdatePublishers: $this->forceUpdatePublishers,
            forceUpdateImages: $this->forceUpdateImages,
            addImages: $this->addImages,
            resizeImages: $this->resizeImages,
            forceUpdateReleaseType: $this->forceUpdateReleaseType,
            forceUpdateReleaseFiles: $this->forceUpdateReleaseFiles,
            forceUpdateReleaseAuthors: $this->forceUpdateReleaseAuthors,
            forceUpdateReleasePublishers: $this->forceUpdateReleasePublishers,
        );
    }

    public function getReleasesByIdList(Builder|null $idList, array|null $sort = [], int|null $start = null, int|null $amount = null): ?array
    {
        return $idList !== null ? $this->loadReleases($idList, $sort, $start, $amount) : null;
    }

    public function makeReleasesQuery(): Builder
    {
        return $this->db->table('module_zxrelease');
    }

    protected function loadReleases(Builder $query, array|null $sort = [], int|null $start = null, int|null $amount = null): array
    {
        if (is_array($sort)) {
            foreach ($sort as $property => $order) {
                if (isset($this->releaseColumnRelations[$property])) {
                    $srcTableName = $this->db->getTablePrefix() . $query->from;
                    foreach ($this->releaseColumnRelations[$property] as $criteria => $orderDirection) {
                        $direction = match ($orderDirection) {
                            true => $order,
                            false => $order === 'desc' ? 'asc' : 'desc',
                            default => $orderDirection,
                        };
                        if ($criteria === 'dateCreated') {
                            $query->leftJoin('structure_elements', 'structure_elements.id', '=', $query->from . '.id');
                            $query->orderBy('structure_elements.dateCreated', $direction);
                        } else {
                            $orderColumn = $criteria === 'title' ? "LOWER($srcTableName.$criteria)" : "$srcTableName.$criteria";
                            $query->orderByRaw("$orderColumn $direction");
                        }
                    }
                }
            }
        }

        $result = [];
        if ($start !== null && $start > 0) {
            $query->offset($start);
        }
        if ($amount !== null) {
            $query->limit($amount);
        }
        if ($records = $query->get()) {
            foreach ($records as $record) {
                if ($zxRelease = $this->manufactureElement($record['id'])) {
                    $this->elementsIndex[$zxRelease->id] = $zxRelease;
                    $result[] = $zxRelease;
                }
            }
        }

        return $result;
    }

    public function joinDeleteZxProd(int $mainZxProdId, int $joinedZxProdId, bool $releasesOnly = false): bool
    {
        if ($joinedZxProdId === $mainZxProdId) {
            return false;
        }
        /**
         * @var zxProdElement $mainZxProd
         */
        if ($mainZxProd = $this->structureManager->getElementById($mainZxProdId)) {
            /**
             * @var zxProdElement $joinedZxProd
             */
            if ($joinedZxProd = $this->structureManager->getElementById($joinedZxProdId)) {
                if ($mainZxProd) {
                    if (!$releasesOnly) {
                        $this->privilegesManager->copyPrivileges($joinedZxProd->id, $mainZxProdId);
                    }
                    //join releases, materials
                    if ($links = $this->linksManager->getElementsLinks($joinedZxProdId, null, 'parent')) {
                        foreach ($links as $link) {
                            $this->linksManager->unLinkElements($joinedZxProdId, $link->childStructureId, $link->type);
                            $this->linksManager->linkElements(
                                $mainZxProd->getPersistedId(),
                                $link->childStructureId,
                                $link->type
                            );
                        }
                    }
                    if (!$releasesOnly) {
                        //join publishers, groups, categories
                        if ($links = $this->linksManager->getElementsLinks($joinedZxProdId, null, 'child')) {
                            foreach ($links as $link) {
                                $this->linksManager->unLinkElements($link->parentStructureId, $joinedZxProdId, $link->type);
                                $this->linksManager->linkElements(
                                    $link->parentStructureId,
                                    $mainZxProd->getPersistedId(),
                                    $link->type
                                );
                            }
                        }

                        if (!$mainZxProd->party) {
                            $mainZxProd->party = $joinedZxProd->party;
                        }
                        if (!$mainZxProd->partyplace) {
                            $mainZxProd->partyplace = $joinedZxProd->partyplace;
                        }
                        if (!$mainZxProd->compo) {
                            $mainZxProd->compo = $joinedZxProd->compo;
                        }
                        if (!$mainZxProd->year) {
                            $mainZxProd->year = $joinedZxProd->year;
                        }
                        if (!$mainZxProd->youtubeId) {
                            $mainZxProd->youtubeId = $joinedZxProd->youtubeId;
                        }
                        if (!$mainZxProd->description) {
                            $mainZxProd->description = $joinedZxProd->description;
                        }
                        if (!$mainZxProd->legalStatus || $mainZxProd->legalStatus === 'unknown') {
                            $mainZxProd->legalStatus = $joinedZxProd->legalStatus;
                        }
                        if (!$mainZxProd->userId) {
                            $mainZxProd->userId = $joinedZxProd->userId;
                        }
                        if (!$mainZxProd->denyVoting) {
                            $mainZxProd->denyVoting = $joinedZxProd->denyVoting;
                        }
                        if (!$mainZxProd->denyComments) {
                            $mainZxProd->denyComments = $joinedZxProd->denyComments;
                        }
                        if (!$mainZxProd->language) {
                            $mainZxProd->language = $joinedZxProd->language;
                        }
                    }

                    $mainZxProd->persistElementData();
                    $mainZxProd->recalculate();
                    if (!$releasesOnly) {
                        //take existing authors
                        $existingAuthorIds = [];
                        if ($existingAuthorShipRecords = $this->authorshipRepository->getElementAuthorsRecords($mainZxProdId)) {
                            foreach ($existingAuthorShipRecords as $record) {
                                $existingAuthorIds[] = $record['authorId'];
                            }
                        }

                        //delete duplicates from joined zxProd
                        if ($existingAuthorIds) {
                            $this->db->table('authorship')
                                ->where('elementId', '=', $joinedZxProdId)
                                ->whereIn('authorId', $existingAuthorIds)
                                ->delete();
                        }
                        //now move all non-duplicated author records to main zxProd
                        $this->db->table('authorship')
                            ->where('elementId', '=', $joinedZxProdId)
                            ->update(['elementId' => $mainZxProd->id]);
                    }
                    //move all import origin IDs to main prod
                    $this->db->table('import_origin')
                        ->where('elementId', '=', $joinedZxProdId)
                        ->update(['elementId' => $mainZxProd->id]);

                    $joinedZxProd->deleteElementData();
                }
            }
        }
        return true;
    }

    public function splitZxProd(int $prodId, array $data): bool|structureElement
    {
        $newProdElement = false;
        /**
         * @var zxProdElement $mainZxProd
         */
        if ($mainZxProd = $this->structureManager->getElementById($prodId)) {
            if ($firstParent = $mainZxProd->getFirstParentElement()) {
                /**
                 * @var zxProdElement $newProdElement
                 */
                if ($newProdElement = $this->structureManager->createElement('zxProd', 'show', $firstParent->id)) {
                    $newProdElement->persistElementData();
                    /*
                     * categories
                     */

                    if ($categoriesIds = $mainZxProd->getConnectedCategoriesIds()) {
                        foreach ($categoriesIds as $categoryId) {
                            $this->linksManager->linkElements($categoryId, $newProdElement->getId(), 'zxProdCategory');
                        }
                    }
                    foreach ($data['properties'] ?? [] as $property => $value) {
                        $newProdElement->$property = $mainZxProd->$property;
                    }
                    $newProdElement->dateAdded = time();
                    $newProdElement->structureName = $newProdElement->title;
                    $newProdElement->persistElementData();

                    if (!empty($data['authors'])) {
                        $authorshipIds = array_keys($data['authors']);
                        $this->authorshipRepository->moveAuthorship($newProdElement->getId(), $authorshipIds);
                    }
                    if (!empty($data['groups'])) {
                        foreach ($data['groups'] as $id => $value) {
                            $this->linksManager->unLinkElements($id, $mainZxProd->id, 'zxProdGroups');
                            $this->linksManager->linkElements($id, $newProdElement->getId(), 'zxProdGroups');
                        }
                    }
                    if (!empty($data['publishers'])) {
                        foreach ($data['publishers'] as $id => $value) {
                            $this->linksManager->unLinkElements($id, $mainZxProd->id, 'zxProdPublishers');
                            $this->linksManager->linkElements($id, $newProdElement->getId(), 'zxProdPublishers');
                        }
                    }
                    if (!empty($data['releases'])) {
                        foreach ($data['releases'] as $id => $value) {
                            $this->linksManager->unLinkElements($mainZxProd->id, $id, 'structure');
                            $this->linksManager->linkElements($newProdElement->getId(), $id, 'structure');
                        }
                    }
                    if (!empty($data['screenshots'])) {
                        foreach ($data['screenshots'] as $id => $value) {
                            $this->linksManager->unLinkElements($mainZxProd->id, $id, 'connectedFile');
                            $this->linksManager->linkElements($newProdElement->getId(), $id, 'connectedFile');
                        }
                    }
                    if (!empty($data['links'])) {
                        foreach ($data['links'] as $string => $value) {
                            $parts = explode(';', $string);
                            $origin = ImportOrigin::tryFrom($parts[0]);
                            $importId = $parts[1] ?? '';
                            if ($origin !== null && $importId !== '') {
                                $this->importIdOperator->moveImportId(
                                    $mainZxProd->id,
                                    $newProdElement->getId(),
                                    $importId,
                                    $origin,
                                    EntityType::Prod
                                );
                            }
                        }
                    }
                    $this->structureManager->clearElementCache($mainZxProd->id);
                }
            }
        }
        return $newProdElement;
    }
}
