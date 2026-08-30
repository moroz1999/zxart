<?php

declare(strict_types=1);

namespace ZxArt\Tests\Forms;

use App\Users\CurrentUser;
use App\Users\CurrentUserService;
use controller;
use LanguagesManager;
use privilegesManager;
use ServerSessionManager;
use PHPUnit\Framework\TestCase;
use structureElement;
use structureManager;
use ZxArt\Forms\FormCreateException;
use ZxArt\Forms\FormCreateService;
use ZxArt\Forms\FormCreateType;
use ZxArt\Forms\FormValidationException;
use ZxArt\Shared\StructureType;

final class FormCreateServiceTest extends TestCase
{
    public function testAuthorDraftUsesCurrentLanguageCatalogue(): void
    {
        $structureManager = $this->createMock(structureManager::class);
        $languagesManager = $this->createMock(LanguagesManager::class);
        $catalogue = $this->createMock(structureElement::class);
        $draft = $this->createStub(structureElement::class);
        $service = new FormCreateService($structureManager, $languagesManager, $this->currentUserService());

        $catalogue->expects($this->once())->method('getId')->willReturn(100);
        $languagesManager->expects($this->once())
            ->method('getCurrentLanguageId')
            ->willReturn(7);
        $structureManager->expects($this->once())
            ->method('getElementsByType')
            ->with(StructureType::AuthorsCatalogue->value, 7)
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
        $service = new FormCreateService($structureManager, $languagesManager, $this->currentUserService());

        $catalogue->expects($this->once())->method('getId')->willReturn(200);
        $languagesManager->expects($this->once())
            ->method('getCurrentLanguageId')
            ->willReturn(7);
        $structureManager->expects($this->once())
            ->method('getElementsByType')
            ->with(StructureType::GroupsCatalogue->value, 7)
            ->willReturn([$catalogue]);
        $structureManager->expects($this->once())
            ->method('createElement')
            ->with(StructureType::Group->value, 'showPublicForm', 200)
            ->willReturn($draft);

        self::assertSame($draft, $service->createDraft(FormCreateType::Group, null));
    }

    public function testAuthorAliasDraftUsesMainAuthorAsParent(): void
    {
        $structureManager = $this->createMock(structureManager::class);
        $languagesManager = $this->createMock(LanguagesManager::class);
        $draft = $this->createStub(structureElement::class);
        $service = new FormCreateService($structureManager, $languagesManager, $this->currentUserService());

        $languagesManager->expects($this->never())->method('getCurrentLanguageId');
        $structureManager->expects($this->once())
            ->method('createElement')
            ->with(StructureType::AuthorAlias->value, 'showPublicForm', 42)
            ->willReturn($draft);

        self::assertSame($draft, $service->createAuthorAliasDraft(42));
    }

    public function testAuthorAliasSubmitAssignsFieldsAndRunsPublicAdd(): void
    {
        $structureManager = $this->createMock(structureManager::class);
        $languagesManager = $this->createStub(LanguagesManager::class);
        $draft = $this->createMock(structureElement::class);
        $controller = $this->createMock(controller::class);
        $service = new FormCreateService($structureManager, $languagesManager, $this->currentUserService());
        $fields = ['title' => 'Alias', 'authorId' => '42'];

        $structureManager->expects($this->once())
            ->method('createElement')
            ->with(StructureType::AuthorAlias->value, 'showPublicForm', 42)
            ->willReturn($draft);
        $draft->expects($this->once())
            ->method('getIdentifier')
            ->willReturn('author/type:authorAlias/action:showPublicForm');
        $draft->expects($this->once())
            ->method('executeAction')
            ->with('publicAdd')
            ->willReturn(true);
        $draft->method('hasActualStructureInfo')->willReturn(true);
        $controller->expects($this->once())
            ->method('setElementFormData')
            ->with('author/type:authorAlias/action:showPublicForm', $fields);

        self::assertSame($draft, $service->submitAuthorAlias(42, $controller, $fields));
    }

    public function testSubmitAssignsFieldsToTemporaryIdAndRunsCreateAction(): void
    {
        $structureManager = $this->createMock(structureManager::class);
        $languagesManager = $this->createMock(LanguagesManager::class);
        $catalogue = $this->createMock(structureElement::class);
        $draft = $this->createMock(structureElement::class);
        $controller = $this->createMock(controller::class);
        $service = new FormCreateService($structureManager, $languagesManager, $this->currentUserService());
        $fields = ['title' => 'New party'];

        $catalogue->expects($this->once())->method('getId')->willReturn(100);
        $draft->expects($this->once())
            ->method('getIdentifier')
            ->willReturn('authors/type:author/action:showPublicForm');
        $draft->expects($this->once())
            ->method('executeAction')
            ->with('publicAdd')
            ->willReturn(true);
        $draft->method('hasActualStructureInfo')->willReturn(true);
        $languagesManager->expects($this->once())
            ->method('getCurrentLanguageId')
            ->willReturn(7);
        $structureManager->expects($this->once())
            ->method('getElementsByType')
            ->with(StructureType::AuthorsCatalogue->value, 7)
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

    public function testSubmitRejectsValuesTheActionDidNotAccept(): void
    {
        $structureManager = $this->createStub(structureManager::class);
        $languagesManager = $this->createStub(LanguagesManager::class);
        $catalogue = $this->createStub(structureElement::class);
        $draft = $this->createStub(structureElement::class);
        $controller = $this->createStub(controller::class);
        $service = new FormCreateService($structureManager, $languagesManager, $this->currentUserService());

        $catalogue->method('getId')->willReturn(100);
        $draft->method('getIdentifier')->willReturn('authors/type:author/action:showPublicForm');
        $draft->method('executeAction')->willReturn(true);
        // a failed validator leaves the element unsaved
        $draft->method('hasActualStructureInfo')->willReturn(false);
        $languagesManager->method('getCurrentLanguageId')->willReturn(7);
        $structureManager->method('getElementsByType')->willReturn([$catalogue]);
        $structureManager->method('createElement')->willReturn($draft);

        $this->expectException(FormValidationException::class);
        $service->submit(FormCreateType::Author, null, $controller, ['title' => '']);
    }

    public function testPressArticleDraftUsesItsProductionAsParent(): void
    {
        $structureManager = $this->createMock(structureManager::class);
        $languagesManager = $this->createMock(LanguagesManager::class);
        $draft = $this->createStub(structureElement::class);
        $service = new FormCreateService($structureManager, $languagesManager, $this->currentUserService());

        $languagesManager->expects($this->never())->method('getCurrentLanguageId');
        $structureManager->expects($this->once())
            ->method('createElement')
            ->with(StructureType::PressArticle->value, 'showPublicForm', 555)
            ->willReturn($draft);

        self::assertSame($draft, $service->createDraft(FormCreateType::PressArticle, null, 555));
    }

    public function testPressArticleSubmitRunsTheActionThatAlsoSavesIt(): void
    {
        $structureManager = $this->createMock(structureManager::class);
        $languagesManager = $this->createStub(LanguagesManager::class);
        $draft = $this->createMock(structureElement::class);
        $controller = $this->createStub(controller::class);
        $service = new FormCreateService($structureManager, $languagesManager, $this->currentUserService());

        $structureManager->method('createElement')->willReturn($draft);
        $draft->method('getIdentifier')->willReturn('prod/type:pressArticle/action:showPublicForm');
        $draft->expects($this->once())
            ->method('executeAction')
            ->with('publicReceive')
            ->willReturn(true);
        $draft->method('hasActualStructureInfo')->willReturn(true);

        self::assertSame(
            $draft,
            $service->submit(FormCreateType::PressArticle, null, $controller, ['title' => 'Review'], 555),
        );
    }

    public function testPressArticleNeedsItsProduction(): void
    {
        $service = new FormCreateService(
            $this->createStub(structureManager::class),
            $this->createStub(LanguagesManager::class),
            $this->currentUserService(),
        );

        $this->expectException(FormCreateException::class);
        $service->createDraft(FormCreateType::PressArticle, null);
    }

    public function testPartyDraftUsesRequestedYearAsParent(): void
    {
        $structureManager = $this->createMock(structureManager::class);
        $languagesManager = $this->createMock(LanguagesManager::class);
        $year = $this->createMock(structureElement::class);
        $draft = $this->createStub(structureElement::class);
        $service = new FormCreateService($structureManager, $languagesManager, $this->currentUserService());

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

    public function testAnonymousVisitorIsToldToAuthenticate(): void
    {
        $structureManager = $this->createStub(structureManager::class);
        $languagesManager = $this->createStub(LanguagesManager::class);
        $catalogue = $this->createStub(structureElement::class);
        $service = new FormCreateService(
            $structureManager,
            $languagesManager,
            $this->currentUserService('anonymous'),
        );

        $catalogue->method('getId')->willReturn(100);
        $languagesManager->method('getCurrentLanguageId')->willReturn(7);
        $structureManager->method('getElementsByType')->willReturn([$catalogue]);
        $structureManager->method('getElementById')->willReturn($catalogue);
        $structureManager->method('createElement')->willReturn(null);

        try {
            $service->createDraft(FormCreateType::Author, null);
            self::fail('An anonymous visitor must not receive a draft');
        } catch (FormCreateException $exception) {
            self::assertSame(401, $exception->getStatusCode());
        }
    }

    public function testMissingPrivilegeNamesTheUserAndTheAction(): void
    {
        $structureManager = $this->createStub(structureManager::class);
        $languagesManager = $this->createStub(LanguagesManager::class);
        $catalogue = $this->createStub(structureElement::class);
        $service = new FormCreateService(
            $structureManager,
            $languagesManager,
            $this->currentUserService('newcomer', 601334),
        );

        $catalogue->method('getId')->willReturn(100);
        $languagesManager->method('getCurrentLanguageId')->willReturn(7);
        $structureManager->method('getElementsByType')->willReturn([$catalogue]);
        $structureManager->method('getElementById')->willReturn($catalogue);
        $structureManager->method('createElement')->willReturn(null);

        try {
            $service->createDraft(FormCreateType::Author, null);
            self::fail('A user without the privilege must not receive a draft');
        } catch (FormCreateException $exception) {
            self::assertSame(403, $exception->getStatusCode());
            self::assertStringContainsString('601334', $exception->getMessage());
            self::assertStringContainsString('author/showPublicForm', $exception->getMessage());
        }
    }

    public function testUnavailableParentIsReportedAsServerError(): void
    {
        $structureManager = $this->createStub(structureManager::class);
        $languagesManager = $this->createStub(LanguagesManager::class);
        $catalogue = $this->createStub(structureElement::class);
        $service = new FormCreateService($structureManager, $languagesManager, $this->currentUserService());

        $catalogue->method('getId')->willReturn(100);
        $languagesManager->method('getCurrentLanguageId')->willReturn(7);
        $structureManager->method('getElementsByType')->willReturn([$catalogue]);
        $structureManager->method('getElementById')->willReturn(null);
        $structureManager->method('createElement')->willReturn(null);

        try {
            $service->createDraft(FormCreateType::Author, null);
            self::fail('An unreachable parent must not produce a draft');
        } catch (FormCreateException $exception) {
            self::assertSame(500, $exception->getStatusCode());
            self::assertStringContainsString('100', $exception->getMessage());
        }
    }

    private function currentUserService(string $userName = 'tester', int $userId = 1): CurrentUserService
    {
        // A real instance, uninitialized: its destructor writes to the session
        // manager, so a doubled CurrentUser would fail on unset typed properties.
        $user = new CurrentUser(
            $this->createStub(privilegesManager::class),
            $this->createStub(ServerSessionManager::class),
        );
        $user->userName = $userName;
        $user->id = $userId;

        $service = $this->createStub(CurrentUserService::class);
        $service->method('getCurrentUser')->willReturn($user);

        return $service;
    }
}
