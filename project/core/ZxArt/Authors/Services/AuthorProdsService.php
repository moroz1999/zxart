<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use authorAliasElement;
use authorElement;
use structureManager;
use zxProdElement;
use zxReleaseElement;
use ZxArt\Authors\Dto\AuthorProdCoAuthorDto;
use ZxArt\Authors\Dto\AuthorProdDto;
use ZxArt\Authors\Repositories\AuthorProdsRepository;
use ZxArt\Prods\Exception\ProdDetailsException;
use ZxArt\Shared\EntityType;

readonly class AuthorProdsService
{
    public function __construct(
        private structureManager $structureManager,
        private AuthorProdsRepository $authorProdsRepository,
    ) {
    }

    /**
     * @return array{items: AuthorProdDto[], total: int, availableRoles: string[]}
     */
    public function getProdsPaged(
        int $authorId,
        int $start,
        int $limit,
        string $sort,
        string $sortDir,
        string $role,
    ): array {
        $author = $this->structureManager->getElementById($authorId);
        if (!($author instanceof authorElement) && !($author instanceof authorAliasElement)) {
            throw new ProdDetailsException('Author or alias not found', 404);
        }

        $page = $this->authorProdsRepository->findPagedByAuthorId($authorId, $start, $limit, $sort, $sortDir, $role);
        $availableRoles = $this->authorProdsRepository->findAvailableRolesByAuthorId($authorId);

        $dtos = [];
        foreach ($page['items'] as $item) {
            $element = $this->structureManager->getElementById($item['id']);
            $dto = match (true) {
                $element instanceof zxProdElement => $this->buildProdDto($element, $item['roles'], $authorId),
                $element instanceof zxReleaseElement => $this->buildReleaseDto($element, $item['roles'], $authorId),
                default => null,
            };
            if ($dto !== null) {
                $dtos[] = $dto;
            }
        }

        return ['items' => $dtos, 'total' => $page['total'], 'availableRoles' => $availableRoles];
    }

    /** @param string[] $rolesInProd */
    private function buildProdDto(zxProdElement $prod, array $rolesInProd, int $currentAuthorId): AuthorProdDto
    {
        return new AuthorProdDto(
            id: (int)$prod->id,
            title: html_entity_decode((string)$prod->getTitle(), ENT_QUOTES),
            url: (string)$prod->getUrl(),
            year: (int)$prod->year,
            thumbnailUrl: $this->resolveProdThumbnail($prod),
            category: $this->resolveProdCategory($prod),
            votes: $prod->getVotes(),
            votesAmount: $prod->getVotesAmount(),
            rolesInProd: $rolesInProd,
            coAuthors: $this->buildProdCoAuthors($prod, $currentAuthorId),
            type: 'prod',
        );
    }

    /** @param string[] $rolesInProd */
    private function buildReleaseDto(zxReleaseElement $release, array $rolesInProd, int $currentAuthorId): AuthorProdDto
    {
        $prod = $release->getProd();
        return new AuthorProdDto(
            id: (int)$release->id,
            title: html_entity_decode((string)$release->getTitle(), ENT_QUOTES),
            url: (string)$release->getUrl(),
            year: (int)$release->year ?: (int)($prod?->year ?? 0),
            thumbnailUrl: $this->resolveReleaseThumbnail($release),
            category: $prod !== null ? $this->resolveProdCategory($prod) : '',
            votes: $release->getVotes(),
            votesAmount: $release->getVotesAmount(),
            rolesInProd: $rolesInProd,
            coAuthors: $this->buildReleaseCoAuthors($release, $currentAuthorId),
            type: 'release',
        );
    }

    private function resolveProdThumbnail(zxProdElement $prod): ?string
    {
        $urls = $prod->getImagesUrls('prodListImage');
        return $urls[0] ?? null;
    }

    private function resolveReleaseThumbnail(zxReleaseElement $release): ?string
    {
        $urls = $release->getImagesUrls('prodListImage');
        return $urls[0] ?? null;
    }

    private function resolveProdCategory(zxProdElement $prod): string
    {
        $categories = $prod->getRootCategoriesInfo();
        return $categories[0]['title'] ?? '';
    }

    /** @return AuthorProdCoAuthorDto[] */
    private function buildProdCoAuthors(zxProdElement $prod, int $currentAuthorId): array
    {
        $coAuthors = [];
        foreach ($prod->getAuthorsInfo(EntityType::Prod->value) as $item) {
            $authorElement = $item['authorElement'] ?? null;
            if ($authorElement === null || (int)$authorElement->id === $currentAuthorId) {
                continue;
            }
            $coAuthors[] = new AuthorProdCoAuthorDto(
                name: html_entity_decode((string)$authorElement->getTitle(), ENT_QUOTES),
                url: (string)$authorElement->getUrl(),
            );
        }
        return $coAuthors;
    }

    /** @return AuthorProdCoAuthorDto[] */
    private function buildReleaseCoAuthors(zxReleaseElement $release, int $currentAuthorId): array
    {
        $coAuthors = [];
        foreach ($release->getAuthorsInfo(EntityType::Release->value) as $item) {
            $authorElement = $item['authorElement'] ?? null;
            if ($authorElement === null || (int)$authorElement->id === $currentAuthorId) {
                continue;
            }
            $coAuthors[] = new AuthorProdCoAuthorDto(
                name: html_entity_decode((string)$authorElement->getTitle(), ENT_QUOTES),
                url: (string)$authorElement->getUrl(),
            );
        }
        return $coAuthors;
    }
}
