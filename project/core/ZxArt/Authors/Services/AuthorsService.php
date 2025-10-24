<?php
declare(strict_types=1);

namespace ZxArt\Authors\Services;

use authorAliasElement;
use authorElement;
use authorsElement;
use ConfigManager;
use CountriesManager;
use ElementsManager;
use fluxbb\cache\Exception;
use groupElement;
use Illuminate\Database\Connection;
use ImportIdOperatorTrait;
use LanguagesManager;
use letterElement;
use LettersElementsListProviderTrait;
use linksManager;
use privilegesManager;
use structureManager;
use TranslitHelper;
use ZxArt\Authors\Repositories\AuthorshipRepository;
use ZxArt\Groups\Services\GroupsService;
use ZxArt\Import\Labels\LabelResolver;
use ZxArt\Import\Labels\PersonLabel;
use ZxArt\LinkTypes;

class AuthorsService extends ElementsManager
{
    use ImportIdOperatorTrait;
    use LettersElementsListProviderTrait;

    protected const string TABLE = 'module_author';
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
        protected GroupsService        $groupsService,
        protected AuthorshipRepository $authorshipRepository,
        private readonly LabelResolver $labelResolver,
    )
    {
        $this->columnRelations = [
            'title' => ['title' => true],
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

    public function importAuthor(PersonLabel $label, string $origin, bool $createNew = true): ?authorElement
    {
        $authorId = $label->id;
        if ($authorId === null) {
            return null;
        }

        $element = $this->resolveAuthorByLabel($label, $origin);

        if ($element === null) {
            if ($resolved = $this->labelResolver->resolve($label)) {
                $element = $resolved;
                if ($origin) {
                    $this->saveImportId($element->id, $authorId, $origin, 'author');
                }
                $this->updateAuthor($element, $label, $origin);
            } elseif ($createNew) {
                $this->createAuthor($label, $origin);
            }
        } else {
            $this->updateAuthor($element, $label, $origin);
        }

        $this->importedAuthors[$origin][$authorId] = $element;
        return $element;
    }

    public function createAuthor(PersonLabel $dto, $origin): ?authorElement
    {
        $title = trim((string)($dto->title ?? ''));
        $realName = trim($dto->realName ?? '');

        if ($title === '' && $realName !== '') {
            $title = $realName;
        }

        if ($title === '') {
            return null;
        }

        $firstLetter = mb_strtolower(mb_substr($title, 0, 1));
        $firstLetter = mb_substr(TranslitHelper::convert($firstLetter), 0, 1);
        if (!preg_match('/[a-zA-Z]/', $firstLetter)) {
            $firstLetter = '#';
        }

        /** @var authorsElement|null $authorsElement */
        $authorsElement = $this->structureManager->getElementByMarker('authors');
        if (!$authorsElement) {
            return null;
        }

        $authorLetterElement = null;
        /** @var letterElement[] $letters */
        $letters = $this->structureManager->getElementsChildren($authorsElement->id);
        foreach ($letters as $letterElement) {
            if (mb_strtolower($letterElement->title) === $firstLetter) {
                $authorLetterElement = $letterElement;
                break;
            }
        }

        if (!$authorLetterElement) {
            return null;
        }

        /** @var authorElement|null $element */
        $element = $this->structureManager->createElement('author', 'show', $authorLetterElement->id);
        if (!$element) {
            return null;
        }

        $this->updateAuthor($element, $dto, $origin);

        if ($origin && $dto->id !== null) {
            $this->saveImportId($element->id, (string)$dto->id, $origin, 'author');
        }
        return $element;
    }

    protected function updateAuthor(authorElement $element, PersonLabel $label, $origin): void
    {
        $changed = false;

        $title = $label->title ?? null;
        if ($title !== null && $title !== '' && $element->title !== $title && !$element->title) {
            $element->title = $title;
            $element->structureName = $title;
            $changed = true;
        }

        if (($this->forceUpdateRealName || !$element->realName) && !empty($label->realName) && $element->realName !== $label->realName) {
            $element->realName = $label->realName;
            $changed = true;
        }

        if (!empty($label->locationName)) {
            $locationElement = $this->countriesManager->getLocationByName($label->locationName);
            if ($locationElement) {
                if ($locationElement->structureType === 'country') {
                    if (!$element->country || $this->forceUpdateCountry) {
                        if ($element->country !== $locationElement->id) {
                            $element->country = $locationElement->id;
                            $changed = true;
                        }
                    }
                } elseif ($locationElement->structureType === 'city') {
                    if (!$element->city || $this->forceUpdateCity) {
                        if ($element->city !== $locationElement->id) {
                            $element->city = $locationElement->id;
                            if ($countryId = $locationElement->getCountryId()) {
                                $element->country = $countryId;
                            }
                            $changed = true;
                        }
                    }
                }
            }
        }

        if (!empty($label->countryName)) {
            $locationElement = $this->countriesManager->getLocationByName($label->countryName);
            if ($locationElement && $locationElement->structureType === 'country') {
                if (($this->forceUpdateCountry || !$element->country) && $element->country != $locationElement->id) {
                    $element->country = $locationElement->id;
                    $changed = true;
                }
            }
        }
        if (!empty($label->cityName)) {
            $locationElement = $this->countriesManager->getLocationByName($label->cityName);
            if ($locationElement) {
                if ($locationElement->structureType === 'city') {
                    if (($this->forceUpdateCity || !$element->city) && $element->city != $locationElement->id) {
                        $element->city = $locationElement->id;
                        $changed = true;
                    }
                }
            }
        }

        if ($this->forceUpdateCountry && $label->countryId !== null) {
            $countryElement = $this->getElementByImportId((string)$label->countryId, $origin, 'country');
            if ($countryElement && $element->country !== $countryElement->id) {
                $element->country = $countryElement->id;
                $changed = true;
            }
        }
        if ($this->forceUpdateCity && $label->cityId !== null) {
            $cityElement = $this->getElementByImportId((string)$label->cityId, $origin, 'city');
            if ($cityElement && $element->city !== $cityElement->id) {
                $element->city = $cityElement->id;
                $changed = true;
            }
        }

        if ($changed) {
            $element->checkCountry();
            $element->persistElementData();
        }

        if ($this->forceUpdateGroups && is_array($label->groupImportIds) && $label->groupImportIds !== []) {
            $roles = $label->groupRoles ?? [];
            foreach ($label->groupImportIds as $groupImportId) {
                if ($groupImportId === null || $groupImportId === '') {
                    continue;
                }
                if ($groupElement = $this->getElementByImportId($groupImportId, $origin, 'group')) {
                    $this->authorshipRepository->addAuthorship($groupElement->id, $element->getId(), 'group', $roles);
                }
            }
        }
    }

    public function importAuthorAlias(PersonLabel $personLabel, string $origin, bool $createNew = true)
    {
        $element = $this->resolveAuthorAliasByLabel($personLabel, $origin);
        if (!$element) {
            if ($element = $this->labelResolver->resolve($personLabel)) {
                if ($origin) {
                    $this->saveImportId($element->id, $importId, $origin, 'author');
                }
                if ($element->structureType === 'authorAlias') {
                    $this->updateAuthorAlias($element, $personLabel, $origin);
                }
            } elseif ($createNew) {
                $element = $this->createAuthorAlias($personLabel, $origin);
            }
        } elseif ($element->structureType === 'authorAlias') {
            $this->updateAuthorAlias($element, $personLabel, $origin);
        }

        $this->importedAuthorAliases[$origin][$importId] = $element;
        return $this->importedAuthorAliases[$origin][$importId];
    }

    /**
     * @deprecated
     */
    public function importAuthorOld(array $authorInfo, string $origin, bool $createNew = true)
    {
        throw new Exception('Deprecated');
//        $dto = PersonLabel::fromArray($authorInfo);
//        return $this->importAuthor($dto, $origin, $createNew);
    }

    protected function createAuthorAlias(PersonLabel $authorAlias, string $origin): ?authorAliasElement
    {
        $title = trim((string)($authorAlias->title ?? ''));
        if ($title === '') {
            return null;
        }

        /** @var authorAliasElement|null $element */
        $element = $this->manufactureAliasElement($title);
        if (!$element) {
            return null;
        }

        $this->updateAuthorAlias($element, $authorAlias, $origin);

        if ($origin && $authorAlias->id !== null) {
            $this->saveImportId($element->id, (string)$authorAlias->id, $origin, 'author');
        }

        return $element;
    }

    protected function manufactureAliasElement(string $title = ''): ?authorAliasElement
    {
        $letterId = $this->getLetterId($title);
        if ($letterId) {
            $letterElement = $this->structureManager->getElementById($letterId);
            if ($letterElement) {
                /**
                 * @var authorAliasElement $element
                 */
                $element = $this->structureManager->createElement('authorAlias', 'show', $letterElement->id);
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
                $element = $this->structureManager->createElement('author', 'show', $letterElement->id);
                return $element;
            }
        }
        return null;
    }

    protected function updateAuthorAlias(authorAliasElement $element, PersonLabel $authorAlias, string $origin): void
    {
        $changed = false;

        $title = trim($authorAlias->title ?? '');
        if ($title !== '' && !$element->title) {
            $element->title = $title;
            $element->structureName = $title;
            $changed = true;
        }

        if ($authorAlias->authorId !== null && !$element->authorId) {
            if ($authorElement = $this->getElementByImportId((string)$authorAlias->authorId, $origin, 'authorAlias')) {
                if ($authorElement->id !== $element->authorId) {
                    $element->authorId = $authorElement->id;
                    $changed = true;
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

    public function resolveAuthorByLabel(PersonLabel $label, string $origin): authorElement|null
    {
        $authorId = $label->id;
        if (isset($this->importedAuthors[$origin][$authorId])) {
            return $this->importedAuthors[$origin][$authorId];
        }

        return $this->getElementByImportId($authorId, $origin, 'author');
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
                    $this->linksManager->linkElements($authorElement->id, $article->id, LinkTypes::PRESS_PEOPLE->value);
                    $this->structureManager->clearElementCache($article->id);
                }
                foreach ($groupElement->getPublisherProds() as $zxProd) {
                    $this->linksManager->linkElements($authorElement->id, $zxProd->id, 'zxProdPublishers');
                    $this->structureManager->clearElementCache($zxProd->id);
                }
                foreach ($groupElement->getGroupProds() as $zxProd) {
                    $this->authorshipRepository->saveAuthorship($zxProd->id, $authorElement->id, 'prod');
                }
                foreach ($groupElement->publishedReleases as $zxRelease) {
                    $this->authorshipRepository->saveAuthorship($zxRelease->id, $authorElement->id, 'release', ['release']);
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

    public function resolveAuthorAliasByLabel(PersonLabel $personLabel, string $origin): authorAliasElement|null
    {
        $importId = (string)$personLabel->id;
        if (isset($this->importedAuthorAliases[$origin][$importId])) {
            return $this->importedAuthorAliases[$origin][$importId];
        }

        return $this->getElementByImportId($importId, $origin, 'authorAlias');
    }
}