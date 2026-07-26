<?php

declare(strict_types=1);

namespace ZxArt\Tests\Authors;

use authorElement;
use controller;
use PHPUnit\Framework\TestCase;
use structureElement;
use structureManager;
use ZxArt\Authors\Dto\AuthorAliasCreateDto;
use ZxArt\Authors\Exception\AuthorAliasFormException;
use ZxArt\Authors\Services\AuthorAliasFormService;
use ZxArt\Forms\FormCreateService;

final class AuthorAliasFormServiceTest extends TestCase
{
    public function testFormContainsDecodedMainAuthorReference(): void
    {
        $structureManager = $this->createMock(structureManager::class);
        $formCreateService = $this->createMock(FormCreateService::class);
        $author = $this->createMock(authorElement::class);
        $service = new AuthorAliasFormService($structureManager, $formCreateService);

        $structureManager->expects($this->once())
            ->method('getElementById')
            ->with(42)
            ->willReturn($author);
        $author->expects($this->once())->method('getId')->willReturn(42);
        $author->expects($this->once())->method('getTitle')->willReturn('A &amp; B');
        $formCreateService->expects($this->once())
            ->method('createAuthorAliasDraft')
            ->with(42)
            ->willReturn($this->createStub(structureElement::class));

        $form = $service->getForm(42);

        self::assertSame(42, $form->author->id);
        self::assertSame('A & B', $form->author->title);
    }

    public function testCreateSubmitsAliasFieldsAndReturnsCreatedId(): void
    {
        $structureManager = $this->createMock(structureManager::class);
        $formCreateService = $this->createMock(FormCreateService::class);
        $author = $this->createStub(authorElement::class);
        $alias = $this->createMock(structureElement::class);
        $controller = $this->createStub(controller::class);
        $service = new AuthorAliasFormService($structureManager, $formCreateService);
        $request = new AuthorAliasCreateDto(
            authorId: 42,
            title: '  Alias  ',
            startDate: '1990',
            endDate: '1992',
            displayInMusic: true,
            displayInGraphics: false,
        );

        $structureManager->expects($this->once())
            ->method('getElementById')
            ->with(42)
            ->willReturn($author);
        $formCreateService->expects($this->once())
            ->method('submitAuthorAlias')
            ->with(
                42,
                $controller,
                [
                    'title' => 'Alias',
                    'authorId' => '42',
                    'startDate' => '1990',
                    'endDate' => '1992',
                    'displayInMusic' => '1',
                    'displayInGraphics' => '',
                ],
            )
            ->willReturn($alias);
        $alias->expects($this->once())->method('getId')->willReturn(73);

        self::assertSame(73, $service->create($request, $controller)->id);
    }

    public function testCreateRequiresAliasTitle(): void
    {
        $structureManager = $this->createStub(structureManager::class);
        $formCreateService = $this->createStub(FormCreateService::class);
        $structureManager->method('getElementById')->willReturn($this->createStub(authorElement::class));
        $service = new AuthorAliasFormService($structureManager, $formCreateService);

        $this->expectException(AuthorAliasFormException::class);
        $this->expectExceptionMessage('Alias title is required');
        $service->create(
            new AuthorAliasCreateDto(42, ' ', '', '', false, false),
            $this->createStub(controller::class),
        );
    }
}
