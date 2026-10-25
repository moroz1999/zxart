<?php

// no strict types: sources hand numeric import ids over as ints, and the
// import has always let PHP coerce them to the strings the lookups take

namespace ZxArt\Releases\Services;

use linksManager;
use ProdsDownloader;
use ZxArt\Authors\Dbal\AuthorshipDbal;
use ZxArt\Import\ImportOrigin;
use ZxArt\Import\Labels\ImportedLabelsImporter;
use ZxArt\Import\Prods\Dto\ReleaseImportDTO;
use ZxArt\Import\Prods\ImportedTitleSanitizer;
use ZxArt\Import\Prods\ProdImportPolicy;
use ZxArt\Import\Services\ImportedAuthorIdResolver;
use ZxArt\Import\Services\ImportedFilesAttacher;
use ZxArt\Import\Services\ImportIdOperator;
use ZxArt\Releases\Repositories\ReleaseRepository;
use ZxArt\Shared\EntityType;
use zxReleaseElement;

/**
 * The change step of a release import: what the source gives goes into the
 * release as the policy allows — all of it into a new one. The release's own
 * fields are saved once, after its credits and before its files.
 */
final readonly class ImportedReleaseMerger
{
    public function __construct(
        private ReleaseRepository $releaseRepository,
        private ImportIdOperator $importIdOperator,
        private ImportedLabelsImporter $importedLabelsImporter,
        private ImportedAuthorIdResolver $importedAuthorIdResolver,
        private ImportedFilesAttacher $importedFilesAttacher,
        private ImportedTitleSanitizer $importedTitleSanitizer,
        private AuthorshipDbal $authorshipDbal,
        private linksManager $linksManager,
        private ProdsDownloader $prodsDownloader,
    ) {
    }

    public function merge(zxReleaseElement $element, ReleaseImportDTO $dto, ImportOrigin $origin, ProdImportPolicy $policy, bool $justCreated): void
    {
        $changed = false;
        $sanitizedTitle = $this->importedTitleSanitizer->sanitize($dto->title);
        if (($policy->forceUpdateTitles || !$element->title) && $sanitizedTitle !== '') {
            $element->title = $sanitizedTitle;
            $element->structureName = $sanitizedTitle;
            $changed = true;
        }
        if ((!$element->year || $policy->forceUpdateYear) && !empty($dto->year)) {
            $element->year = $dto->year;
            $changed = true;
        }
        if (!$element->hardwareRequired && !empty($dto->hardwareRequired)) {
            $element->hardwareRequired = array_unique($dto->hardwareRequired);
            $changed = true;
        }
        if (($policy->forceUpdateReleaseType || $justCreated) && (!$element->releaseType || $element->releaseType === 'unknown') && !empty($dto->releaseType)) {
            $element->releaseType = $dto->releaseType;
            $changed = true;
        }
        if (!$element->language && !empty($dto->languages)) {
            $element->language = $dto->languages;
            $changed = true;
        }
        if (!$element->version && !empty($dto->version)) {
            $element->version = $dto->version;
            $changed = true;
        }

        if (($policy->forceUpdateReleaseFiles || $justCreated)) {
            if (!empty($dto->filePath)) {
                $destinationFolder = $element->getUploadedFilesPath();
                $info = pathinfo($dto->filePath);
                $element->file = $element->getPersistedId();
                $element->fileName = $info['filename'] . '.' . $info['extension'];
                $element->parsed = 0;
                $changed = true;
                copy($dto->filePath, $destinationFolder . $element->file);
            } elseif (!empty($dto->fileUrl)) {
                $info = pathinfo($dto->fileUrl);
                $fileName = !empty($dto->fileName)
                    ? $dto->fileName
                    : ($info['filename'] . '.' . ($info['extension'] ?? ''));
                if ($element->fileName != $fileName || !is_file($element->getFilePath())) {
                    $changed = true;
                    $element->file = $element->getPersistedId();
                    $element->fileName = $fileName;
                    $element->parsed = 0;
                    $this->prodsDownloader->moveFileContents($element->getFilePath(), $dto->fileUrl);
                }
            }
        }

        if (!empty($dto->description) && !$element->description) {
            $element->description = $dto->description;
            $changed = true;
        }

        if (($policy->forceUpdateReleasePublishers || $policy->forceUpdateReleaseAuthors || $justCreated) && !empty($dto->labels)) {
            $this->importedLabelsImporter->import($dto->labels, $origin);
        }

        $releasePublishers = $dto->publishers ?? [];
        $releaseAuthors = $dto->authors ?? [];

        if (!empty($dto->undetermined)) {
            foreach ($dto->undetermined as $undeterminedId => $roles) {
                if ($this->importIdOperator->getElementIdByImportId($undeterminedId, $origin, EntityType::Group)) {
                    if (!in_array($undeterminedId, $releasePublishers, true)) {
                        $releasePublishers[] = $undeterminedId;
                    }
                } elseif ($this->importIdOperator->getElementIdByImportId($undeterminedId, $origin, EntityType::Author)) {
                    if (!array_key_exists($undeterminedId, $releaseAuthors)) {
                        $releaseAuthors[$undeterminedId] = $roles;
                    }
                }
            }
        }

        if ($policy->forceUpdateReleaseAuthors || (($justCreated) && !empty($releaseAuthors))) {
            if ($policy->forceUpdateReleaseAuthors || !$element->getAuthorsInfo(EntityType::Release->value)) {
                foreach ($releaseAuthors as $importAuthorId => $roles) {
                    $authorId = $this->importedAuthorIdResolver->resolve($importAuthorId, $origin, $element);
                    if ($authorId !== null) {
                        $this->authorshipDbal->addAuthorship($element->getId(), $authorId, EntityType::Release, $roles);
                    }
                }
            }
        }

        if ($policy->forceUpdateReleasePublishers || (($justCreated) && !empty($releasePublishers))) {
            if ($policy->forceUpdateReleasePublishers || !$element->getPublishersIds()) {
                foreach ($releasePublishers as $importPublisherId) {
                    if ($publisherId = $this->importIdOperator->getElementIdByImportId($importPublisherId, $origin, EntityType::Group)) {
                        $this->linksManager->linkElements($publisherId, $element->getId(), 'zxReleasePublishers');
                    } elseif ($publisherId = $this->importIdOperator->getElementIdByImportId($importPublisherId, $origin, EntityType::Author)) {
                        $this->linksManager->linkElements($publisherId, $element->getId(), 'zxReleasePublishers');
                    }
                }
            }
        }

        if ($changed) {
            $this->releaseRepository->save($element);
        }

        if ($dto->images !== null && ($policy->forceUpdateImages || $justCreated || !$element->getFilesList('screenshotsSelector'))) {
            $this->importedFilesAttacher->attachUrls($element, $dto->images, 'screenshotsSelector', $policy->addImages);
        }
        if ($dto->inlayImages !== null) {
            $this->importedFilesAttacher->attachUrls($element, $dto->inlayImages, 'inlayFilesSelector', $policy->addImages);
        }
        if ($dto->infoFiles !== null) {
            $this->importedFilesAttacher->attachUrls($element, $dto->infoFiles, 'infoFilesSelector', $policy->addImages);
        }
        if ($dto->adFiles !== null) {
            $this->importedFilesAttacher->attachUrls($element, $dto->adFiles, 'adFilesSelector', $policy->addImages);
        }
        if ($dto->fileUrl !== null) {
            $this->prodsDownloader->removeFile($dto->fileUrl);
        }
    }
}
