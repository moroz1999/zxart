<?php

declare(strict_types=1);

namespace ZxArt\Tests\Authors;

use authorElement;
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
}
