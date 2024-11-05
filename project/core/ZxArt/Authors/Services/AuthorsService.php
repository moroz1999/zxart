<?php

namespace ZxArt\Authors\Services;

use authorAliasElement;
use authorElement;
use authorsElement;
use ConfigManager;
use CountriesManager;
use ElementsManager;
use groupElement;
use Illuminate\Database\Connection;
use ImportIdOperatorTrait;
use LanguagesManager;
use letterElement;
use LettersElementsListProviderTrait;
use linksManager;
use privilegesManager;
use structureElement;
use structureManager;
use TranslitHelper;
use ZxArt\Authors\Repositories\AuthorshipRepository;
use ZxArt\Groups\Services\GroupsService;
use ZxArt\Import\Labels\Label;
use ZxArt\Import\Labels\LabelResolver;
use ZxArt\Import\Labels\LabelType;

class AuthorsService extends ElementsManager
{
    use ImportIdOperatorTrait;
    use LettersElementsListProviderTrait;

    protected const TABLE = 'module_author';
    protected bool $forceUpdateRealName = false;
    protected bool $forceUpdateCountry = false;
    protected bool $forceUpdateCity = false;
    protected bool $forceUpdateGroups = false;
    protected array $columnRelations = [];
    protected array $importedAuthors = [];
    protected array $importedAuthorAliases = [];

    public function __construct(
        protected linksManager         $linksManager,
        protected LanguagesManager     $languagesManager,
        protected ConfigManager        $configManager,
        protected CountriesManager     $countriesManager,
        protected privilegesManager    $privilegesManager,
        protected Connection           $db,
        protected structureManager     $structureManager,
        protected GroupsService        $groupsManager,
        protected AuthorshipRepository $authorshipRepository,
        private readonly LabelResolver $labelResolver,
    )
    {
        $this->columnRelations = [
            'title' => ['LOWER(title)' => true],
            'date' => ['id' => true],
            'graphicsRating' => ['graphicsRating' => true, 'title' => false],
            'musicRating' => ['musicRating' => true, 'title' => false],
        ];
    }

    public function setForceUpdateRealName(bool $forceUpdateRealName): void
    {
        $this->forceUpdateRealName = $forceUpdateRealName;
    }

    public function setForceUpdateGroups(bool $forceUpdateGroups): void
    {
        $this->forceUpdateGroups = $forceUpdateGroups;
    }

    public function setForceUpdateCountry(bool $forceUpdateCountry): void
    {
        $this->forceUpdateCountry = $forceUpdateCountry;
    }

    public function setForceUpdateCity(bool $forceUpdateCity): void
    {
        $this->forceUpdateCity = $forceUpdateCity;
    }

    /**
     * @psalm-param array{id: string|int, title: string, countryName?: string, cityName?: string} $authorInfo
     */
    public function importAuthor(array $authorInfo, $origin, $createNew = true)
    {
        if (!isset($this->importedAuthors[$origin][$authorInfo['id']])) {
            /**
             * @var authorElement $element
             */
            if (!($element = $this->getElementByImportId($authorInfo['id'], $origin, 'author'))) {
                $label = new Label(
                    id: $authorInfo['id'] ?? null,
                    name: $authorInfo['title'],
                    city: $authorInfo['cityName'] ?? null,
                    country: $authorInfo['countryName'] ?? null,
                    type: LabelType::person,
                    isAlias: false
                );
                if ($element = $this->labelResolver->resolve($label)) {
                    if ($origin) {
                        $this->saveImportId($element->id, $authorInfo['id'], $origin, 'author');
                    }
                    $this->updateAuthor($element, $authorInfo, $origin);
                } elseif ($createNew) {
                    $element = $this->createAuthor($authorInfo, $origin);
                }
            } else {
                $this->updateAuthor($element, $authorInfo, $origin);
            }
            $this->importedAuthors[$origin][$authorInfo['id']] = $element;
        }
        return $this->importedAuthors[$origin][$authorInfo['id']];
    }

    /**
     * @param array $authorInfo
     */
    public function createAuthor($authorInfo, $origin): ?authorElement
    {
        $element = null;
        $title = $authorInfo['title'] ?? null;
        $realName = $authorInfo['realName'] ?? null;
        if (($title === null || $title === '') && $realName !== null){
            $authorInfo['title'] = $realName;
        }
        $firstLetter = mb_strtolower(mb_substr($authorInfo['title'], 0, 1));
        $firstLetter = mb_substr(TranslitHelper::convert($firstLetter), 0, 1);
        if (!preg_match('/[a-zA-Z]/', $firstLetter)) {
            $firstLetter = '#';
        }
        /**
         * @var authorsElement $authorsElement
         */
        if ($authorsElement = $this->structureManager->getElementByMarker('authors')) {
            $authorLetterElement = null;
            /**
             * @var letterElement[] $letters
             */
            $letters = $this->structureManager->getElementsChildren($authorsElement->id);
            foreach ($letters as $letterElement) {
                if (mb_strtolower($letterElement->title) === $firstLetter) {
                    $authorLetterElement = $letterElement;
                    break;
                }
            }
            /**
             * @var authorElement $element
             */
            if ($authorLetterElement && ($element = $this->structureManager->createElement('author', 'show', $authorLetterElement->id))) {
                $this->updateAuthor($element, $authorInfo, $origin);
                if ($origin) {
                    $this->saveImportId($element->id, $authorInfo['id'], $origin, 'author');
                }
            }
        }
        return $element;
    }

    /**
     * @param authorElement $element
     */
    protected function updateAuthor($element, array $authorInfo, $origin): void
    {
        $changed = false;
        if (!empty($authorInfo['title']) && $element->title !== $authorInfo['title']) {
            if (!$element->title) {
                $changed = true;
                $element->title = $authorInfo['title'];
                $element->structureName = $authorInfo['title'];
            }
        }
        if (!empty($authorInfo['locationName'])) {
            if ($locationElement = $this->countriesManager->getLocationByName($authorInfo['locationName'])) {
                if ($locationElement->structureType === 'country') {
                    if (!$element->country || $this->forceUpdateCountry) {
                        if ($element->country != $locationElement->id) {
                            $changed = true;
                            $element->country = $locationElement->id;
                        }
                    }
                } elseif ($locationElement->structureType === 'city') {
                    if (!$element->city || $this->forceUpdateCity) {
                        if ($element->city != $locationElement->id) {
                            $changed = true;
                            $element->city = $locationElement->id;
                            if ($countryId = $locationElement->getCountryId()) {
                                $element->country = $countryId;
                            }
                        }
                    }
                }
            }
        }
        if (!empty($authorInfo['countryName'])) {
            if ($locationElement = $this->countriesManager->getLocationByName($authorInfo['countryName'])) {
                if ($locationElement->structureType === 'country') {
                    if ($element->country != $locationElement->id) {
                        $changed = true;
                        $element->country = $locationElement->id;
                    }
                }
            }
        }
        if (!empty($authorInfo['cityName'])) {
            if ($locationElement = $this->countriesManager->getLocationByName($authorInfo['cityName'])) {
                if ($locationElement->structureType === 'city') {
                    if ($element->city != $locationElement->id) {
                        $changed = true;
                        $element->city = $locationElement->id;
                    }
                }
            }
        }
        if ($this->forceUpdateCountry && !empty($authorInfo['countryId'])) {
            $countryElement = $this->getElementByImportId($authorInfo['countryId'], $origin, 'country');
            if ($countryElement && $element->country != $countryElement->id) {
                $changed = true;
                $element->country = $countryElement->id;
            }
        }

        if (($this->forceUpdateRealName || !$element->realName) && !empty($authorInfo['realName'])) {
            $changed = true;
            $element->realName = $authorInfo['realName'];
        }

        if ($changed) {
            $element->checkCountry();
            $element->persistElementData();
        }

        if ($this->forceUpdateGroups && isset($authorInfo['groupsIds'])) {
            foreach ($authorInfo['groupsIds'] as $groupId) {
                if ($groupElement = $this->getElementByImportId($groupId, $origin, 'group')) {
                    $this->authorshipRepository->checkAuthorship($groupElement->id, $element->getId(), 'group');
                }
            }
        }
        if (isset($authorInfo['groups'])) {
            foreach ($authorInfo['groups'] as $groupData) {
                if ($groupElement = $this->groupsManager->importGroup($groupData, $origin)) {
                    $this->authorshipRepository->checkAuthorship($groupElement->id, $element->getId(), 'group');
                }
            }
        }
    }

    public function importAuthorAlias($authorAliasInfo, $origin, bool $createNew = true)
    {
        if (!isset($this->importedAuthorAliases[$origin][$authorAliasInfo['id']])) {
            /**
             * @var authorAliasElement $element
             */
            if (!($element = $this->getElementByImportId($authorAliasInfo['id'], $origin, 'author'))) {
                $label = new Label(
                    id: $authorAliasInfo['id'] ?? null,
                    name: $authorAliasInfo['title'],
                    type: LabelType::person,
                    isAlias: true
                );
                if ($element = $this->labelResolver->resolve($label)) {
                    if ($origin) {
                        $this->saveImportId($element->id, $authorAliasInfo['id'], $origin, 'author');
                    }
                    $this->updateAuthorAlias($element, $authorAliasInfo, $origin);
                } elseif ($createNew) {
                    $element = $this->createAuthorAlias($authorAliasInfo, $origin);
                }
            } else {
                $this->updateAuthorAlias($element, $authorAliasInfo, $origin);
            }
            $this->importedAuthorAliases[$origin][$authorAliasInfo['id']] = $element;
        }
        return $this->importedAuthorAliases[$origin][$authorAliasInfo['id']];
    }

    protected function createAuthorAlias(array $authorAliasInfo, $origin): ?authorAliasElement
    {
        if ($element = $this->manufactureAliasElement($authorAliasInfo['title'])) {
            $this->updateAuthorAlias($element, $authorAliasInfo, $origin);
            $this->saveImportId($element->id, $authorAliasInfo['id'], $origin, 'author');
        }
        return $element;
    }

    protected function manufactureAliasElement(string $title = ''): ?authorAliasElement
    {
        if ($letterId = $this->getLetterId($title)) {
            if ($letterElement = $this->structureManager->getElementById($letterId)) {
                if ($element = $this->structureManager->createElement('authorAlias', 'show', $letterElement->id)) {
                    return $element;
                }
            }
        }
        return null;
    }

    public function manufactureAuthorElement(string $title): ?structureElement
    {
        if ($letterId = $this->getLetterId($title)) {
            if ($letterElement = $this->structureManager->getElementById($letterId)) {
                if ($element = $this->structureManager->createElement('author', 'show', $letterElement->id)) {
                    return $element;
                }
            }
        }
        return null;
    }

    protected function updateAuthorAlias(authorAliasElement $element, array $authorAliasInfo, $origin): void
    {
        $changed = false;

        if (!empty($authorAliasInfo['title']) && !$element->title) {
            if (!$element->title) {
                $changed = true;

                $element->title = $authorAliasInfo['title'];
                $element->structureName = $authorAliasInfo['title'];
            }
        }
        if (!empty($authorAliasInfo['authorId'])) {
            if (isset($authorAliasInfo['authorId']) && !$element->authorId) {
                $authorElement = $this->getElementByImportId($authorAliasInfo['authorId'], $origin, 'author');
                if (!empty($authorElement) && $authorElement->id != $element->authorId) {
                    $changed = true;
                    $element->authorId = $authorElement->id;
                }
            }
        }
        if ($changed) {
            $element->persistElementData();
        }
    }

    public function joinDeleteAuthor(int $mainAuthorId, int $joinedAuthorId): void
    {
        $this->joinAuthor($mainAuthorId, $joinedAuthorId, false);
    }

    public function joinAuthorAsAlias(int $mainAuthorId, int $joinedAuthorId): void
    {
        $this->joinAuthor($mainAuthorId, $joinedAuthorId, true);
    }

    protected function joinAuthor($mainAuthorId, $joinedAuthorId, bool $makeAlias = true): bool
    {
        if ($joinedAuthorId == $mainAuthorId) {
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
                    $targetAuthorElement->title = $joinedAuthor->title;
                    $targetAuthorElement->structureName = $joinedAuthor->title;
                    $targetAuthorElement->authorId = $mainAuthorId;
                } else {
                    $targetAuthorElement = $mainAuthor;
                }

                if ($targetAuthorElement) {
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
                                $targetAuthorElement->getId(),
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
                        if (!$mainAuthor->artCityId) {
                            $mainAuthor->artCityId = $joinedAuthor->artCityId;
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
                        ->update(['authorId' => $targetAuthorElement->id]);

                    $this->db->table('import_origin')
                        ->where('elementId', '=', $joinedAuthorId)
                        ->update(['elementId' => $targetAuthorElement->id]);

                    $joinedAuthor->deleteElementData();
                }
            }
        }
        return true;
    }

    public function convertAliasToAuthor(int $aliasId): authorElement|false
    {
        $newAuthorElement = false;
        /**
         * @var authorAliasElement $aliasElement
         */
        if ($aliasElement = $this->structureManager->getElementById($aliasId)) {
            /**
             * @var authorElement $newAuthorElement
             */
            if ($newAuthorElement = $this->manufactureAuthorElement($aliasElement->title)) {
                $this->privilegesManager->copyPrivileges($aliasId, $aliasId);

                if ($links = $this->linksManager->getElementsLinks($aliasId, null, 'parent')) {
                    foreach ($links as $link) {
                        $this->linksManager->unLinkElements($aliasId, $link->childStructureId, $link->type);
                        $this->linksManager->linkElements(
                            $newAuthorElement->getId(),
                            $link->childStructureId,
                            $link->type
                        );
                    }
                }

                $newAuthorElement->title = $aliasElement->title;
                $newAuthorElement->structureName = $aliasElement->title;
                $newAuthorElement->persistElementData();

                $this->db->table('authorship')
                    ->where('authorId', '=', $aliasId)
                    ->update(['authorId' => $newAuthorElement->id]);

                $this->db->table('import_origin')
                    ->where('elementId', '=', $aliasId)
                    ->update(['elementId' => $newAuthorElement->id]);

                $aliasElement->deleteElementData();
            }
        }

        return $newAuthorElement;
    }

    /**
     * @psalm-param 'admin'|'public' $type
     *
     *
     */
    protected function getLettersListMarker(string $type): string
    {
        if ($type === 'admin') {
            return 'authors';
        } else {
            return 'authorsmenu';
        }
    }


    public function convertGroupToAuthor(groupElement $groupElement): authorElement|structureElement|bool|null
    {
        $authorElement = false;
        if ($groupElement->structureType === 'group') {
            if ($authorElement = $this->manufactureAuthorElement($groupElement->title)) {
                $authorElement->title = $groupElement->title;
                $authorElement->structureName = $groupElement->title;
                $authorElement->country = $groupElement->country;
                $authorElement->city = $groupElement->city;
                $authorElement->persistElementData();

                foreach ($groupElement->getPublisherProds() as $zxProd) {
                    $this->linksManager->linkElements($authorElement->id, $zxProd->id, 'zxProdPublishers');
                }
                foreach ($groupElement->getGroupProds() as $zxProd) {
                    $this->authorshipRepository->checkAuthorship($zxProd->id, $authorElement->id, 'prod', []);
                }
                foreach ($groupElement->publishedReleases as $zxRelease) {
                    $this->authorshipRepository->checkAuthorship($zxRelease->id, $authorElement->id, 'release', ['release']);
                }

                if ($records = $this->db->table('import_origin')
                    ->where('elementId', '=', $groupElement->id)
                    ->get()) {
                    foreach ($records as $record) {
                        if (!$this->db->table('import_origin')
                            ->where('type', '=', 'author')
                            ->where('importOrigin', '=', $record['importOrigin'])
                            ->where('importId', '=', $record['importId'])
                            ->limit(1)
                            ->get()) {
                            $this->db->table('import_origin')
                                ->where('elementId', '=', $groupElement->id)
                                ->update(
                                    [
                                        'elementId' => $authorElement->id,
                                        'type' => 'author',
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