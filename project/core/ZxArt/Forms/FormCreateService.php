<?php

declare(strict_types=1);

namespace ZxArt\Forms;

use controller;
use LanguagesManager;
use RuntimeException;
use structureElement;
use structureManager;
use ZxArt\Shared\StructureType;

final readonly class FormCreateService
{
    public function __construct(
        private structureManager $structureManager,
        private LanguagesManager $languagesManager,
    ) {
    }

    public function createDraft(FormCreateType $formType, ?int $year, ?int $parentId = null): structureElement
    {
        return $this->createElement($formType, $year, $parentId);
    }

    public function createAuthorAliasDraft(int $authorId): structureElement
    {
        return $this->createTransientElement(
            StructureType::AuthorAlias,
            'showPublicForm',
            $authorId,
        );
    }

    /**
     * @param array<array-key, mixed> $fields
     */
    public function submit(
        FormCreateType $formType,
        ?int $year,
        controller $controller,
        array $fields,
        ?int $parentId = null,
    ): structureElement
    {
        $element = $this->createElement($formType, $year, $parentId);
        return $this->submitElement(
            $element,
            $this->getSubmitAction($formType),
            $controller,
            $fields,
            !$formType->isBatchUpload(),
        );
    }

    /**
     * @param array<array-key, mixed> $fields
     */
    public function submitAuthorAlias(
        int $authorId,
        controller $controller,
        array $fields,
    ): structureElement
    {
        $element = $this->createAuthorAliasDraft($authorId);
        return $this->submitElement($element, 'publicAdd', $controller, $fields);
    }

    /**
     * @param array<array-key, mixed> $fields
     */
    private function submitElement(
        structureElement $element,
        string $submitAction,
        controller $controller,
        array $fields,
        bool $persistsItself = true,
    ): structureElement
    {
        $temporaryElementId = $element->getIdentifier();
        $controller->setElementFormData($temporaryElementId, $fields);

        $isSubmitted = $element->executeAction($submitAction);
        if ($isSubmitted !== true) {
            throw new RuntimeException('Form submission is forbidden');
        }
        // The action reports a failed validator only by leaving the element
        // unsaved (it falls back to re-rendering the form), so an element that is
        // still transient means the submitted values were rejected. Batch upload
        // forms are never persisted themselves — they raise their own error when
        // no work could be created.
        if ($persistsItself && !$element->hasActualStructureInfo()) {
            throw new FormValidationException('Form values are not accepted');
        }

        return $element;
    }

    private function createElement(FormCreateType $formType, ?int $year, ?int $parentId): structureElement
    {
        $structureType = match ($formType) {
            FormCreateType::Author => StructureType::Author,
            FormCreateType::Group => StructureType::Group,
            FormCreateType::Party => StructureType::Party,
            FormCreateType::ProdBatch => StructureType::ZxProdsUploadForm,
            FormCreateType::PictureBatch => StructureType::PicturesUploadForm,
            FormCreateType::MusicBatch => StructureType::MusicUploadForm,
            FormCreateType::Release => StructureType::ZxRelease,
        };
        // A batch upload started from an author, group or party attaches its works
        // to that element, so the form is created under it. Without a parent the
        // works land in the catalogue root.
        $parentId ??= match ($formType) {
            FormCreateType::Author => $this->getCatalogue(StructureType::AuthorsCatalogue)->getId(),
            FormCreateType::Group => $this->getCatalogue(StructureType::GroupsCatalogue)->getId(),
            FormCreateType::Party => $this->getPartyYear($year)->getId(),
            FormCreateType::ProdBatch => $this->getCatalogue(StructureType::ZxProdCategoriesCatalogue)->getId(),
            FormCreateType::PictureBatch => $this->getCatalogue(StructureType::PicturesCatalogue)->getId(),
            FormCreateType::MusicBatch => $this->getCatalogue(StructureType::MusicCatalogue)->getId(),
            FormCreateType::Release => throw new RuntimeException('A release needs its production'),
        };

        return $this->createTransientElement($structureType, $this->getFormAction($formType), $parentId);
    }

    private function createTransientElement(
        StructureType $structureType,
        string $formAction,
        int $parentId,
    ): structureElement
    {
        $element = $this->structureManager->createElement($structureType->value, $formAction, $parentId);
        if (!$element instanceof structureElement) {
            throw new RuntimeException('Form creation is forbidden');
        }

        return $element;
    }

    private function getFormAction(FormCreateType $formType): string
    {
        return $formType->isBatchUpload() ? 'batchUploadForm' : 'showPublicForm';
    }

    private function getSubmitAction(FormCreateType $formType): string
    {
        return $formType->isBatchUpload() ? 'batchUpload' : 'publicAdd';
    }

    /**
     * Each language branch has its own catalogue element, so the lookup must not
     * be limited: the limit applies to the type query before the in-parent check
     * and would hide the catalogue of every language but the lowest id one.
     */
    private function getCatalogue(StructureType $catalogueType): structureElement
    {
        $catalogues = $this->structureManager->getElementsByType(
            $catalogueType->value,
            (int)$this->languagesManager->getCurrentLanguageId(),
        );
        $catalogue = reset($catalogues);
        if (!$catalogue instanceof structureElement) {
            throw new RuntimeException($catalogueType->value . ' not found');
        }

        return $catalogue;
    }

    private function getPartyYear(?int $year): structureElement
    {
        if ($year === null || $year <= 0) {
            throw new RuntimeException('Party year is required');
        }
        foreach ($this->structureManager->getElementsByType(StructureType::Year->value) as $yearElement) {
            if ((int)$yearElement->getTitle() === $year) {
                return $yearElement;
            }
        }

        throw new RuntimeException('Party year not found');
    }
}
