<?php

declare(strict_types=1);

namespace ZxArt\Tests\Forms;

use controller;
use LanguagesManager;
use PHPUnit\Framework\TestCase;
use structureElement;
use structureManager;
use ZxArt\Forms\FormCreateService;
use ZxArt\Forms\FormCreateType;
use ZxArt\Shared\StructureType;

final class FormCreateServiceTest extends TestCase
{
    public function testAuthorDraftUsesCurrentLanguageCatalogue(): void
    {
        $structureManager = $this->createMock(structureManager::class);
        $languagesManager = $this->createMock(LanguagesManager::class);
        $catalogue = $this->createMock(structureElement::class);
        $draft = $this->createStub(structureElement::class);
        $service = new FormCreateService($structureManager, $languagesManager);

        $catalogue->expects($this->once())->method('getId')->willReturn(100);
        $languagesManager->expects($this->once())
            ->method('getCurrentLanguageId')
            ->willReturn(7);
        $structureManager->expects($this->once())
            ->method('getElementsByType')
            ->with(StructureType::AuthorsCatalogue->value, 7, [], 1)
            ->willReturn([$catalogue]);
        $structureManager->expects($this->once())
            ->method('createElement')
            ->with(StructureType::Author->value, 'showPublicForm', 100)
            ->willReturn($draft);

        self::assertSame($draft, $service->createDraft(FormCreateType::Author, null));
    }

    public function testGroupDraftUsesGroupsCatalogue(): void
    {
        $structureManager = $this->createMock(structureManager::class);
        $languagesManager = $this->createMock(LanguagesManager::class);
        $catalogue = $this->createMock(structureElement::class);
        $draft = $this->createStub(structureElement::class);
        $service = new FormCreateService($structureManager, $languagesManager);

        $catalogue->expects($this->once())->method('getId')->willReturn(200);
        $languagesManager->expects($this->once())
            ->method('getCurrentLanguageId')
            ->willReturn(7);
        $structureManager->expects($this->once())
            ->method('getElementsByType')
            ->with(StructureType::GroupsCatalogue->value, 7, [], 1)
            ->willReturn([$catalogue]);
        $structureManager->expects($this->once())
            ->method('createElement')
            ->with(StructureType::Group->value, 'showPublicForm', 200)
            ->willReturn($draft);

        self::assertSame($draft, $service->createDraft(FormCreateType::Group, null));
    }

    public function testSubmitAssignsFieldsToTemporaryIdAndRunsCreateAction(): void
    {
        $structureManager = $this->createMock(structureManager::class);
        $languagesManager = $this->createMock(LanguagesManager::class);
        $catalogue = $this->createMock(structureElement::class);
        $draft = $this->createMock(structureElement::class);
        $controller = $this->createMock(controller::class);
        $service = new FormCreateService($structureManager, $languagesManager);
        $fields = ['title' => 'New party'];

        $catalogue->expects($this->once())->method('getId')->willReturn(100);
        $draft->expects($this->once())
            ->method('getIdentifier')
            ->willReturn('authors/type:author/action:showPublicForm');
        $draft->expects($this->once())
            ->method('executeAction')
            ->with('publicAdd')
            ->willReturn(true);
        $languagesManager->expects($this->once())
            ->method('getCurrentLanguageId')
            ->willReturn(7);
        $structureManager->expects($this->once())
            ->method('getElementsByType')
            ->with(StructureType::AuthorsCatalogue->value, 7, [], 1)
            ->willReturn([$catalogue]);
        $structureManager->expects($this->once())
            ->method('createElement')
            ->with(StructureType::Author->value, 'showPublicForm', 100)
            ->willReturn($draft);
        $controller->expects($this->once())
            ->method('setElementFormData')
            ->with('authors/type:author/action:showPublicForm', $fields);

        self::assertSame($draft, $service->submit(FormCreateType::Author, null, $controller, $fields));
    }

    public function testPartyDraftUsesRequestedYearAsParent(): void
    {
        $structureManager = $this->createMock(structureManager::class);
        $languagesManager = $this->createMock(LanguagesManager::class);
        $year = $this->createMock(structureElement::class);
        $draft = $this->createStub(structureElement::class);
        $service = new FormCreateService($structureManager, $languagesManager);

        $year->expects($this->once())->method('getTitle')->willReturn('2026');
        $year->expects($this->once())->method('getId')->willReturn(20);
        $languagesManager->expects($this->never())->method('getCurrentLanguageId');
        $structureManager->expects($this->once())
            ->method('getElementsByType')
            ->with(StructureType::Year->value)
            ->willReturn([$year]);
        $structureManager->expects($this->once())
            ->method('createElement')
            ->with(StructureType::Party->value, 'showPublicForm', 20)
            ->willReturn($draft);

        self::assertSame($draft, $service->createDraft(FormCreateType::Party, 2026));
    }
}
