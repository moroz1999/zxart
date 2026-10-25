<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use authorElement;
use RuntimeException;
use structureManager;
use ZxArt\Authors\Dto\AuthorAliasFormAuthorDto;
use ZxArt\Authors\Dto\AuthorAliasFormDto;
use ZxArt\Authors\Exception\AuthorAliasFormException;
use ZxArt\Forms\FormCreateService;

final readonly class AuthorAliasFormService
{
    public function __construct(
        private structureManager $structureManager,
        private FormCreateService $formCreateService,
    ) {
    }

    public function getForm(int $authorId): AuthorAliasFormDto
    {
        $author = $this->getAuthor($authorId);
        try {
            $this->formCreateService->createAuthorAliasDraft($authorId);
        } catch (RuntimeException $exception) {
            throw new AuthorAliasFormException('Forbidden', 403, $exception);
        }

        return new AuthorAliasFormDto(
            author: new AuthorAliasFormAuthorDto(
                id: $author->getId(),
                title: $this->decode($author->getTitle()),
            ),
        );
    }

    private function getAuthor(int $authorId): authorElement
    {
        if ($authorId <= 0) {
            throw new AuthorAliasFormException('Missing required author id', 400);
        }
        $author = $this->structureManager->getElementById($authorId);
        if (!$author instanceof authorElement) {
            throw new AuthorAliasFormException('Author not found', 404);
        }

        return $author;
    }

    private function decode(string $value): string
    {
        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
