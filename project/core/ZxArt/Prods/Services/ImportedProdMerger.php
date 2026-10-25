<?php

// no strict types: sources hand numeric import ids over as ints, and the
// import has always let PHP coerce them to the strings the lookups take

namespace ZxArt\Prods\Services;

use linksManager;
use pressArticleElement;
use structureManager;
use ZxArt\Authors\Dbal\AuthorshipRepository;
use ZxArt\Import\ImportOrigin;
use ZxArt\Import\Labels\ImportedLabelsImporter;
use ZxArt\Import\Prods\Dto\ProdImportDTO;
use ZxArt\Import\Prods\ImportedTitleSanitizer;
use ZxArt\Import\Prods\ProdImportPolicy;
use ZxArt\Import\Services\ImportedAuthorIdResolver;
use ZxArt\Import\Services\ImportedFilesAttacher;
use ZxArt\Import\Services\ImportIdOperator;
use ZxArt\Parties\Services\PartiesService;
use ZxArt\Prods\Repositories\ProdRepository;
use ZxArt\Shared\EntityType;
use zxProdElement;

/**
 * The change step of a production import: what the source gives goes into the
 * production as the policy allows — all of it into a new one. The
 * production's own fields are saved once, before its links, credits, articles
 * and files.
 */
final readonly class ImportedProdMerger
{
    public function __construct(
        private ProdRepository $prodRepository,
        private PartiesService $partiesService,
        private ImportIdOperator $importIdOperator,
        private ImportedLabelsImporter $importedLabelsImporter,
        private ImportedAuthorIdResolver $importedAuthorIdResolver,
        private ImportedFilesAttacher $importedFilesAttacher,
        private ImportedTitleSanitizer $importedTitleSanitizer,
        private ImportedProdCategoriesUpdater $importedProdCategoriesUpdater,
        private AuthorshipRepository $authorshipRepository,
        private linksManager $linksManager,
        private structureManager $structureManager,
    ) {
    }

    public function merge(zxProdElement $element, ProdImportDTO $dto, ImportOrigin $origin, ProdImportPolicy $policy, bool $justCreated): void
    {
        $changed = false;
        $dtoTitle = $this->importedTitleSanitizer->sanitize($dto->title ?? '');
        if ($dtoTitle !== '' && ($element->title !== $dtoTitle)) {
            if (!$element->title || $policy->forceUpdateTitles) {
                $element->title = $dtoTitle;
                $element->structureName = $dtoTitle;
                $changed = true;
            }
        }
        if (!empty($dto->altTitle) && ($element->altTitle !== $dto->altTitle)) {
            $element->altTitle = $dto->altTitle;
            $changed = true;
        }
        if (
            ($dto->legalStatus !== null) &&
            (empty($element->legalStatus) || $element->legalStatus === 'unknown' || $justCreated) &&
            $element->legalStatus !== $dto->legalStatus->name
        ) {
            $element->legalStatus = $dto->legalStatus->name;
            $changed = true;
        }
        if (!empty($dto->year) && (($element->year != $dto->year) && (!$element->year || $policy->forceUpdateYear || $justCreated))) {
            $element->year = $dto->year;
            $changed = true;
        }
        if (!empty($dto->compo) && ($element->compo != $dto->compo)) {
            $element->compo = $dto->compo;
            $changed = true;
        }
        if (!empty($dto->description) && !$element->description) {
            $element->description = $dto->description;
            $changed = true;
        }
        if (!$element->hardwareRequired && !empty($dto->hardwareRequired)) {
            $element->hardwareRequired = array_unique($dto->hardwareRequired);
            $changed = true;
        }
        if ($dto->htmlDescription !== null) {
            $element->htmlDescription = $dto->htmlDescription;
            $changed = true;
        }
        if ($dto->htmlInstructions !== null) {
            $element->htmlInstructions = $dto->htmlInstructions;
            $changed = true;
        }
        if ($dto->instructions !== null && ($element->instructions === '' || $element->instructions === null)) {
            $element->instructions = $dto->instructions;
            $changed = true;
        }
        if (!empty($dto->party) && (!$element->party || (!empty($dto->party->place) && !$element->partyplace))) {
            $partyTitle = $dto->party->title ?? null;
            $partyYear = $dto->party->year ?? null;
            if ($partyTitle && $partyYear) {
                if ($partyElement = $this->partiesService->getPartyByTitleAndYear($partyTitle, $partyYear)) {
                    if ($element->party !== $partyElement->getId()) {
                        $element->party = $partyElement->getId();
                        $element->renewPartyLink();
                        $changed = true;
                    }
                    if (!empty($dto->party->place)) {
                        if ($element->partyplace !== $dto->party->place) {
                            $element->partyplace = $dto->party->place;
                            $changed = true;
                        }
                    }
                    if ($dto->party->website !== null) {
                        $partyElement->website = $dto->party->website;
                        $partyElement->persistElementData();
                    }
                }
            }
        }
        if (!empty($dto->youtubeId) && ($element->youtubeId != $dto->youtubeId) && ($policy->forceUpdateYoutube || $justCreated)) {
            $element->youtubeId = $dto->youtubeId;
            $changed = true;
        }
        if (!empty($dto->externalLink) && (($element->externalLink != $dto->externalLink) && ($policy->forceUpdateExternalLink || $justCreated))) {
            $element->externalLink = $dto->externalLink;
            $changed = true;
        }
        if (!empty($dto->languages) && $element->language != $dto->languages) {
            $element->language = $dto->languages;
            $changed = true;
        }

        if ($changed) {
            $this->prodRepository->save($element);
        }

        if (!empty($dto->labels) && ($policy->forceUpdatePublishers || $policy->forceUpdateGroups || $policy->forceUpdateAuthors || $justCreated)) {
            $this->importedLabelsImporter->import($dto->labels, $origin);
        }

        if (!empty($dto->directCategories)) {
            if ($policy->forceUpdateCategories || $justCreated || !$element->getConnectedCategoriesIds()) {
                foreach ($dto->directCategories as $categoryId) {
                    $this->linksManager->linkElements($categoryId, $element->getId(), 'zxProdCategory');
                }
            }
        }

        if (!empty($dto->undetermined)) {
            foreach ($dto->undetermined as $undeterminedId => $roles) {
                $existingElementId = $this->importIdOperator->getElementIdByImportId($undeterminedId, $origin, EntityType::Group);
                if ($existingElementId === null) {
                    $authorId = $this->importedAuthorIdResolver->resolve($undeterminedId, $origin, $element);
                    if ($authorId !== null) {
                        $this->authorshipRepository->addAuthorship($element->getId(), $authorId, EntityType::Prod, $roles);
                    }
                } else {
                    $this->linksManager->linkElements($existingElementId, $element->getId(), 'zxProdGroups');
                }
            }
        }

        $authorsInfo = $element->getAuthorsInfo(EntityType::Prod->value);
        $authorRoles = $dto->authorRoles ?? [];
        if (($policy->forceUpdateAuthors || $justCreated || !$authorsInfo) && ($authorRoles !== [])) {
            foreach ($authorRoles as $importAuthorId => $roles) {
                $authorId = $this->importedAuthorIdResolver->resolve($importAuthorId, $origin, $element);
                if ($authorId !== null) {
                    $this->authorshipRepository->addAuthorship($element->getId(), $authorId, EntityType::Prod, $roles);
                }
            }
        }

        if (!empty($dto->groups) && (!$element->groups || $policy->forceUpdateGroups)) {
            $linksIndex = $this->linksManager->getElementsLinksIndex($element->getId(), 'zxProdGroups', 'child');
            foreach ($dto->groups as $importGroupId) {
                if ($groupId = $this->importIdOperator->getElementIdByImportId($importGroupId, $origin, EntityType::Group)) {
                    if (!isset($linksIndex[$groupId])) {
                        $this->linksManager->linkElements($groupId, $element->getId(), 'zxProdGroups');
                    }
                    unset($linksIndex[$groupId]);
                }
            }
            foreach ($linksIndex as $link) {
                $link->delete();
            }
        }

        if (!empty($dto->publishers) && (!$element->publishers || $policy->forceUpdatePublishers)) {
            $linksIndex = $this->linksManager->getElementsLinksIndex($element->getId(), 'zxProdPublishers', 'child');
            foreach ($dto->publishers as $importPublisherId) {
                $publisherId = $this->importIdOperator->getElementIdByImportId($importPublisherId, $origin, EntityType::Group)
                    ?: $this->importIdOperator->getElementIdByImportId($importPublisherId, $origin, EntityType::Author);
                if ($publisherId) {
                    if (!isset($linksIndex[$publisherId])) {
                        $this->linksManager->linkElements($publisherId, $element->getId(), 'zxProdPublishers');
                    }
                    unset($linksIndex[$publisherId]);
                }
            }
            foreach ($linksIndex as $link) {
                $link->delete();
            }
        }

        if (!empty($dto->compilationItems)) {
            if (!$element->compilationItems) {
                foreach ($dto->compilationItems as $importItemId) {
                    if ($importItemId === null) {
                        continue;
                    }

                    if ($prodId = $this->importIdOperator->getElementIdByImportId($importItemId, $origin, EntityType::Prod)) {
                        $this->linksManager->linkElements($element->getId(), $prodId, 'compilation');
                    } elseif ($releaseId = $this->importIdOperator->getElementIdByImportId($importItemId, $origin, EntityType::Release)) {
                        $this->linksManager->linkElements($element->getId(), $releaseId, 'compilation');
                    }
                }
            }
        }

        if (!empty($dto->seriesProdIds) && !$element->seriesProds) {
            foreach ($dto->seriesProdIds as $importItemId) {
                if ($prodId = $this->importIdOperator->getElementIdByImportId($importItemId, $origin, EntityType::Prod)) {
                    $this->linksManager->linkElements($element->getId(), $prodId, 'series');
                }
            }
        }

        if (!empty($dto->articles)) {
            if (!$element->articles) {
                foreach ($dto->articles as $article) {
                    /**
                     * @var pressArticleElement $articleElement
                     */
                    if ($articleElement = $this->structureManager->createElement(
                        'pressArticle',
                        'showForm',
                        $element->getPersistedId(),
                        false,
                        'prodArticle'
                    )) {
                        $articleElement->title = $article->title;
                        $articleElement->structureName = $article->title;
                        $articleElement->introduction = $article->introduction;
                        $articleElement->externalLink = $article->externalLink;
                        $articleElement->content = $article->content;
                        $articleElement->persistElementData();
                    }
                }
            }
        }

        if (!empty($dto->categories) && (!$element->getConnectedCategoriesIds() || $policy->forceUpdateCategories || $justCreated)) {
            $this->importedProdCategoriesUpdater->update($element->getId(), $dto->categories, $origin);
        }

        if (!empty($dto->images) && ($policy->forceUpdateImages || $justCreated || !$element->getFilesList('connectedFile'))) {
            $this->importedFilesAttacher->attachUrls($element, $dto->images, 'connectedFile', $policy->addImages);
            if ($policy->resizeImages) {
                $element->resizeImages();
            }
        }

        if (!empty($dto->maps) && ($policy->forceUpdateImages || $justCreated || !$element->getFilesList('mapFilesSelector'))) {
            $this->importedFilesAttacher->attachWithAuthors($element, $dto->maps, 'mapFilesSelector');
        }

        if (!empty($dto->inlayImages) && ($policy->forceUpdateImages || $justCreated || !$element->getFilesList('inlayFilesSelector'))) {
            $this->importedFilesAttacher->attachUrls($element, $dto->inlayImages, 'inlayFilesSelector', $policy->addImages);
        }

        if (!empty($dto->rzx)) {
            $this->importedFilesAttacher->attachWithAuthors($element, $dto->rzx, 'rzx');
        }

        if (!empty($dto->importIds)) {
            foreach ($dto->importIds as $importOrigin => $id) {
                $knownOrigin = ImportOrigin::tryFrom((string)$importOrigin);
                if ($knownOrigin === null) {
                    continue;
                }
                if (!$this->importIdOperator->getElementIdByImportId($id, $knownOrigin, EntityType::Prod)) {
                    $this->importIdOperator->saveImportId($element->getPersistedId(), $id, $knownOrigin, EntityType::Prod);
                }
            }
        }
    }
}
