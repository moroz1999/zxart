<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use authorElement;
use controller;
use RuntimeException;
use structureManager;
use ZxArt\Authors\Dto\AuthorAliasCreateDto;
use ZxArt\Authors\Dto\AuthorAliasCreatedDto;
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

    public function create(AuthorAliasCreateDto $request, controller $controller): AuthorAliasCreatedDto
    {
        $this->getAuthor($request->authorId);
        $title = trim($request->title);
        if ($title === '') {
            throw new AuthorAliasFormException('Alias title is required', 400);
        }

        try {
            $alias = $this->formCreateService->submitAuthorAlias(
                $request->authorId,
                $controller,
                [
                    'title' => $title,
                    'authorId' => (string)$request->authorId,
                    'startDate' => $request->startDate,
                    'endDate' => $request->endDate,
                    'displayInMusic' => $request->displayInMusic ? '1' : '',
                    'displayInGraphics' => $request->displayInGraphics ? '1' : '',
                ],
            );
        } catch (RuntimeException $exception) {
            throw new AuthorAliasFormException('Forbidden', 403, $exception);
        }

        return new AuthorAliasCreatedDto($alias->getId());
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
