<?php
declare(strict_types=1);

namespace ZxArt\Authors\Services;

use authorAliasElement;
use authorElement;
use ConfigManager;
use ElementsManager;
use fluxbb\cache\Exception;
use groupElement;
use Illuminate\Database\Connection;
use LanguagesManager;
use LettersElementsListProviderTrait;
use linksManager;
use privilegesManager;
use structureManager;
use ZxArt\Authors\Dbal\AuthorshipRepository;
use ZxArt\Groups\Services\GroupsService;
use ZxArt\LinkTypes;
use ZxArt\Shared\EntityType;
use ZxArt\Users\ClaimedAuthorService;

class AuthorsService extends ElementsManager
{
    use LettersElementsListProviderTrait;

    protected const string TABLE = 'module_author';
    protected array $columnRelations = [];

    public function __construct(
        protected readonly linksManager         $linksManager,
        protected LanguagesManager              $languagesManager,
        protected readonly ConfigManager        $configManager,
        protected readonly privilegesManager    $privilegesManager,
        protected Connection                    $db,
        protected structureManager              $structureManager,
        protected readonly GroupsService        $groupsService,
        protected readonly AuthorshipRepository $authorshipRepository,
        private readonly ClaimedAuthorService   $claimedAuthorService,
    )
    {
        $this->columnRelations = [
            'title' => ['title' => true],
            'date' => ['id' => true],
            'graphicsRating' => ['graphicsRating' => true, 'title' => false],
            'musicRating' => ['musicRating' => true, 'title' => false],
        ];
    }

    /**
     * @deprecated
     */
    public function importAuthorOld(): void
    {
        throw new Exception('Deprecated');
//        $dto = PersonLabel::fromArray($authorInfo);
//        return $this->importAuthor($dto, $origin, $createNew);
    }

    public function manufactureAliasElement(string $title = ''): ?authorAliasElement
    {
        $letterId = $this->getLetterId($title);
        if ($letterId) {
            $letterElement = $this->structureManager->getElementById($letterId);
            if ($letterElement) {
                /**
                 * @var authorAliasElement $element
                 */
                $element = $this->structureManager->createElement('authorAlias', 'show', $letterElement->getId());
                if ($element) {
                    return $element;
                }
            }
        }
        return null;
    }

    public function manufactureAuthorElement(string $title): ?authorElement
    {
        $letterId = $this->getLetterId($title);
        if ($letterId) {
            $letterElement = $this->structureManager->getElementById($letterId);
            if ($letterElement) {
                /**
                 * @var authorElement $element
                 */
                $element = $this->structureManager->createElement('author', 'show', $letterElement->getId());
                return $element;
            }
        }
        return null;
    }

    public function joinDeleteAuthor(int $mainAuthorId, int $joinedAuthorId): void
    {
        $this->joinAuthor($mainAuthorId, $joinedAuthorId, false);
    }

    public function joinAuthorAsAlias(int $mainAuthorId, int $joinedAuthorId): void
    {
        $this->joinAuthor($mainAuthorId, $joinedAuthorId, true);
    }

    protected function joinAuthor(int $mainAuthorId, int $joinedAuthorId, bool $makeAlias = true): bool
    {
        if ($joinedAuthorId === $mainAuthorId) {
            return false;
        }
        /**
         * @var authorAliasElement|authorElement $mainAuthor
         */
        if ($mainAuthor = $this->structureManager->getElementById($mainAuthorId)) {
            /**
             * @var authorAliasElement|authorElement $joinedAuthor
             */
            if ($joinedAuthor = $this->structureManager->getElementById($joinedAuthorId)) {
                /**
                 * @var authorAliasElement|authorElement $targetAuthorElement
                 */
                if ($makeAlias) {
                    $targetAuthorElement = $this->manufactureAliasElement($joinedAuthor->title);
                    if ($targetAuthorElement) {
                        $targetAuthorElement->title = $joinedAuthor->title;
                        $targetAuthorElement->structureName = $joinedAuthor->title;
                        $targetAuthorElement->authorId = $mainAuthorId;
                    }
                } else {
                    $targetAuthorElement = $mainAuthor;
                }

                if ($targetAuthorElement) {
                    if ($joinedAuthor instanceof authorElement) {
                        $joinedAuthor->resetAliasElements();
                    }
                    if ($aliasElements = $joinedAuthor->getAliasElements()) {
                        /**
                         * @var authorAliasElement $aliasElement
                         */
                        foreach ($aliasElements as $aliasElement) {
                            $aliasElement->authorId = $mainAuthorId;
                            $aliasElement->persistElementData();
                        }
                    }

                    $this->privilegesManager->copyPrivileges($joinedAuthor->id, $mainAuthorId);

                    if ($links = $this->linksManager->getElementsLinks($joinedAuthorId, null, 'parent')) {
                        foreach ($links as $link) {
                            $this->linksManager->unLinkElements($joinedAuthorId, $link->childStructureId, $link->type);
                            $this->linksManager->linkElements(
                                $targetAuthorElement->getPersistedId(),
                                $link->childStructureId,
                                $link->type
                            );
                        }
                    }
                    foreach ($this->languagesManager->getLanguagesIdList() as $languageId) {
                        if (!$mainAuthor->getValue('realName', $languageId)) {
                            if ($joinedValue = $joinedAuthor->getValue('realName', $languageId)) {
                                $mainAuthor->setValue('realName', $joinedValue, $languageId);
                            }
                        }
                    }

                    $mainAuthor->articles = array_merge($mainAuthor->articles, $joinedAuthor->articles);

                    if ($joinedAuthor->structureType === 'author') {
                        if (!$mainAuthor->country) {
                            $mainAuthor->country = $joinedAuthor->country;
                        }
                        if (!$mainAuthor->city) {
                            $mainAuthor->city = $joinedAuthor->city;
                        }
                        if (empty($mainAuthor->zxTunesId) && !empty($joinedAuthor->zxTunesId)) {
                            $mainAuthor->zxTunesId = $joinedAuthor->zxTunesId;
                        }
                        if (!$mainAuthor->wikiLink) {
                            $mainAuthor->wikiLink = $joinedAuthor->wikiLink;
                        }
                        if (!$mainAuthor->email) {
                            $mainAuthor->email = $joinedAuthor->email;
                        }
                        if (!$mainAuthor->site) {
                            $mainAuthor->site = $joinedAuthor->site;
                        }
                        $mainAuthor->persistElementData();
                    } else {
                        $mainAuthor->persistElementData();
                    }

                    if ($targetAuthorElement !== $mainAuthor) {
                        $targetAuthorElement->persistElementData();
                    }

                    //disabled authorship moving to new author
                    //check if author already has authorship in same elements. we dont need duplicates
                    $existingElements = [];
                    if ($existingAuthorShipRecords = $this->authorshipRepository->getAuthorshipRecords($mainAuthorId)) {
                        foreach ($existingAuthorShipRecords as $record) {
                            $existingElements[] = $record['elementId'];
                        }
                    }

                    //delete duplicates from joined author
                    if ($existingElements) {
                        $this->db->table('authorship')
                            ->where('authorId', '=', $joinedAuthorId)
                            ->whereIn('elementId', $existingElements)
                            ->delete();
                    }

                    //now move all remaining records to main author
                    $this->db->table('authorship')
                        ->where('authorId', '=', $joinedAuthorId)
                        ->update(['authorId' => $targetAuthorElement->getId()]);

                    $this->db->table('import_origin')
                        ->where('elementId', '=', $joinedAuthorId)
                        ->update(['elementId' => $targetAuthorElement->getId()]);

                    //accounts claiming the absorbed author now belong to the surviving one
                    $this->claimedAuthorService->reassign($joinedAuthorId, $mainAuthorId);

                    $joinedAuthor->deleteElementData();
                }
            }
        }
        return true;
    }

    public function convertAliasToAuthor(authorAliasElement $aliasElement): ?authorElement
    {
        $newAuthorElement = $this->manufactureAuthorElement($aliasElement->title);
        if ($newAuthorElement === null) {
            return null;
        }
        $aliasId = $aliasElement->getId();

        $newAuthorElement->title = $aliasElement->title;
        $newAuthorElement->structureName = $aliasElement->title;
        $newAuthorElement->persistElementData();
        $newAuthorId = $newAuthorElement->getId();

        $this->privilegesManager->copyPrivileges($aliasId, $newAuthorId);

        if ($links = $this->linksManager->getElementsLinks($aliasId, null, 'parent')) {
            foreach ($links as $link) {
                $this->linksManager->unLinkElements($aliasId, $link->childStructureId, $link->type);
                $this->linksManager->linkElements($newAuthorId, $link->childStructureId, $link->type);
            }
        }

        $this->db->table('authorship')
            ->where('authorId', '=', $aliasId)
            ->update(['authorId' => $newAuthorId]);

        $this->db->table('import_origin')
            ->where('elementId', '=', $aliasId)
            ->update(['elementId' => $newAuthorId]);

        $aliasElement->deleteElementData();

        return $newAuthorElement;
    }

    /**
     * @psalm-param 'admin'|'public' $type
     *
     *
     */
    protected function getLettersListMarker(string $type): string
    {
        return $type === 'admin' ? 'authors' : 'authorsmenu';
    }

    public function convertGroupToAuthor(groupElement $groupElement): authorElement|null
    {
        $authorElement = null;
        if ($groupElement->structureType === 'group') {
            $authorElement = $this->manufactureAuthorElement($groupElement->title);
            if ($authorElement) {
                $authorElement->title = $groupElement->title;
                $authorElement->structureName = $groupElement->title;
                $authorElement->country = $groupElement->country;
                $authorElement->city = $groupElement->city;
                $authorElement->persistElementData();
                foreach ($groupElement->mentions as $article) {
                    $this->linksManager->linkElements($authorElement->getId(), $article->id, LinkTypes::PRESS_PEOPLE->value);
                    $this->structureManager->clearElementCache($article->id);
                }
                foreach ($groupElement->getPublisherProds() as $zxProd) {
                    $this->linksManager->linkElements($authorElement->getId(), $zxProd->id, 'zxProdPublishers');
                    $this->structureManager->clearElementCache($zxProd->id);
                }
                foreach ($groupElement->getGroupProds() as $zxProd) {
                    $this->authorshipRepository->saveAuthorship($zxProd->id, $authorElement->getId(), EntityType::Prod);
                }
                foreach ($groupElement->publishedReleases as $zxRelease) {
                    $this->authorshipRepository->saveAuthorship($zxRelease->id, $authorElement->getId(), EntityType::Release, ['release']);
                }

                if ($records = $this->db->table('import_origin')
                    ->where('elementId', '=', $groupElement->getId())
                    ->get()) {
                    foreach ($records as $record) {
                        if (!$this->db->table('import_origin')
                            ->where('type', '=', EntityType::Author->value)
                            ->where('importOrigin', '=', $record['importOrigin'])
                            ->where('importId', '=', $record['importId'])
                            ->limit(1)
                            ->get()) {
                            $this->db->table('import_origin')
                                ->where('elementId', '=', $groupElement->getId())
                                ->update(
                                    [
                                        'elementId' => $authorElement->getId(),
                                        'type' => EntityType::Author->value,
                                    ]
                                );
                        } else {
                            $this->db->table('import_origin')
                                ->where('id', '=', $record['id'])->delete();
                        }
                    }
                }

                $groupElement->deleteElementData();
            }
        }
        return $authorElement;
    }
}