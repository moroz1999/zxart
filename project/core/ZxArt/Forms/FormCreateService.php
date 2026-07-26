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

    public function createDraft(FormCreateType $formType, ?int $year): structureElement
    {
        return $this->createElement($formType, $year);
    }

    /**
     * @param array<array-key, mixed> $fields
     */
    public function submit(
        FormCreateType $formType,
        ?int $year,
        controller $controller,
        array $fields,
    ): structureElement
    {
        $element = $this->createElement($formType, $year);
        $temporaryElementId = $element->getIdentifier();
        $controller->setElementFormData($temporaryElementId, $fields);

        $submitAction = $this->getSubmitAction($formType);
        $isSubmitted = $element->executeAction($submitAction);
        if ($isSubmitted !== true) {
            throw new RuntimeException('Form submission is forbidden');
        }

        return $element;
    }

    private function createElement(FormCreateType $formType, ?int $year): structureElement
    {
        $structureType = match ($formType) {
            FormCreateType::Author => StructureType::Author,
            FormCreateType::Group => StructureType::Group,
            FormCreateType::Party => StructureType::Party,
            FormCreateType::ProdBatch => StructureType::ZxProdsUploadForm,
        };
        $parent = match ($formType) {
            FormCreateType::Author => $this->getCatalogue(StructureType::AuthorsCatalogue),
            FormCreateType::Group => $this->getCatalogue(StructureType::GroupsCatalogue),
            FormCreateType::Party => $this->getPartyYear($year),
            FormCreateType::ProdBatch => $this->getCatalogue(StructureType::ZxProdCategoriesCatalogue),
        };
        $element = $this->structureManager->createElement(
            $structureType->value,
            $this->getFormAction($formType),
            $parent->getId(),
        );
        if (!$element instanceof structureElement) {
            throw new RuntimeException('Form creation is forbidden');
        }

        return $element;
    }

    private function getFormAction(FormCreateType $formType): string
    {
        return $formType === FormCreateType::ProdBatch ? 'batchUploadForm' : 'showPublicForm';
    }

    private function getSubmitAction(FormCreateType $formType): string
    {
        return $formType === FormCreateType::ProdBatch ? 'batchUpload' : 'publicAdd';
    }

    private function getCatalogue(StructureType $catalogueType): structureElement
    {
        $catalogues = $this->structureManager->getElementsByType(
            $catalogueType->value,
            (int)$this->languagesManager->getCurrentLanguageId(),
            [],
            1,
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
