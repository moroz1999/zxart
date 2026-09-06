<?php

declare(strict_types=1);

namespace ZxArt\ZxProdCategories;

use LanguagesManager;
use linksManager;
use structureElement;
use structureManager;
use ZxArt\LinkTypes;
use ZxArt\Shared\StructureType;
use ZxArt\ZxProdCategories\Dto\CategoryNodeDto;
use ZxArt\ZxProdCategories\Dto\CategorySaveDto;
use ZxArt\ZxProdCategories\Exception\CategoryException;
use ZxArt\ZxProdCategories\Repositories\CategoryManageRepository;
use zxProdCategoriesCatalogueElement;
use zxProdCategoryElement;

/**
 * Creating, renaming, moving and removing prod categories.
 *
 * The tree lives under the single `zxProdCategories` container and is held
 * together by `structure` links. A **top-level** category carries a second,
 * `softCatalogue` link from each language's software folder — that projection
 * is what the public catalogue lists, and the admin catalogue form rebuilds it
 * wholesale. Nothing recomputes it on its own, so every write that changes
 * whether a category is top-level maintains those links itself.
 *
 * `parentId` therefore always states where the category belongs, on a creation
 * and on an update alike: `null` means top-level.
 */
final class CategoryManageService
{
    private const int MAX_TITLE_LENGTH = 255;

    /** @var list<array{parentId: int, categoryId: int}>|null */
    private ?array $treeLinks = null;
    /** @var array<int, array<int, string>>|null */
    private ?array $titles = null;
    /** @var array<int, int>|null */
    private ?array $prodCounts = null;

    public function __construct(
        private readonly CategoryManageRepository $repository,
        private readonly structureManager $structureManager,
        private readonly linksManager $linksManager,
        private readonly LanguagesManager $languagesManager,
    ) {
    }

    /**
     * The whole tree as a depth-first list, each node carrying its level.
     *
     * @return list<CategoryNodeDto>
     */
    public function getTree(): array
    {
        $titles = $this->getTitles();
        $prodCounts = $this->getProdCounts();
        $currentLanguageId = (int)$this->languagesManager->getCurrentLanguageId();

        // links arrive in display order, and a link whose parent is not itself a
        // category is what makes its child a top-level one
        $childrenByParent = [];
        $rootIds = [];
        foreach ($this->getTreeLinks() as $link) {
            $childrenByParent[$link['parentId']][] = $link['categoryId'];
            if (!isset($titles[$link['parentId']])) {
                $rootIds[] = $link['categoryId'];
            }
        }

        $subtreeCounts = [];
        foreach ($rootIds as $categoryId) {
            $this->sumSubtree($categoryId, $childrenByParent, $prodCounts, $subtreeCounts);
        }

        $nodes = [];
        foreach ($rootIds as $categoryId) {
            $this->collectBranch(
                $categoryId,
                null,
                0,
                $titles,
                $childrenByParent,
                $prodCounts,
                $subtreeCounts,
                $currentLanguageId,
                $nodes,
            );
        }

        return $nodes;
    }

    /**
     * @throws CategoryException
     */
    public function create(CategorySaveDto $request): int
    {
        $titles = $this->validateTitles($request->titles);
        $parent = $this->resolveParent($request->parentId);

        $element = $this->structureManager->createElement(
            StructureType::ZxProdCategory->value,
            'show',
            $parent->getId(),
        );
        if (!$element instanceof zxProdCategoryElement) {
            throw new CategoryException('Category could not be created', 500);
        }

        $this->applyTitles($element, $titles);
        // persistElementData has already cleared the parent's cache through
        // persistStructureLinks; only the catalogue projection is written here
        if ($request->parentId === null) {
            $this->linkToCatalogues($element->getId());
        }
        $this->resetCache();

        return $element->getId();
    }

    /**
     * @throws CategoryException
     */
    public function update(CategorySaveDto $request): void
    {
        $id = $request->id
            ?? throw new CategoryException('Missing required field: id', 400);
        $titles = $this->validateTitles($request->titles);
        $element = $this->getCategory($id);

        $this->applyTitles($element, $titles);
        $this->moveTo($element, $request->parentId);
        $this->resetCache();
    }

    /**
     * A category holding productions or subcategories is never removed on the
     * way past: deletion cascades down the `structure` links, so a mistaken
     * click would take a whole branch with it.
     *
     * @throws CategoryException
     */
    public function delete(int $id): void
    {
        $element = $this->getCategory($id);

        $prods = $this->getProdCounts()[$id] ?? 0;
        if ($prods > 0) {
            throw new CategoryException(
                'Category still holds ' . $prods . ' production(s) and cannot be deleted',
                409,
            );
        }
        if ($this->getChildIds($id) !== []) {
            throw new CategoryException('Category still has subcategories and cannot be deleted', 409);
        }

        // deleteElementData drops every link and clears the parents' caches
        $element->deleteElementData();
        $this->resetCache();
    }

    /**
     * Re-links the category under its new parent and keeps the catalogue
     * projection in step: a category is listed there exactly while it is
     * top-level.
     *
     * @throws CategoryException
     */
    private function moveTo(zxProdCategoryElement $element, ?int $parentId): void
    {
        $id = $element->getId();
        $place = $this->getCurrentPlace($id);
        $currentParentId = $place['isTopLevel'] ? null : $place['parentId'];
        if ($currentParentId === $parentId) {
            return;
        }

        if ($parentId !== null && in_array($parentId, $this->getSubtreeIds($id), true)) {
            throw new CategoryException('A category cannot be moved into its own subtree', 409);
        }

        $newParent = $this->resolveParent($parentId);
        $this->linksManager->unLinkElements($place['parentId'], $id, LinkTypes::STRUCTURE->value);
        $this->linksManager->linkElements($newParent->getId(), $id, LinkTypes::STRUCTURE->value);

        if ($parentId === null) {
            $this->linkToCatalogues($id);
        } elseif ($place['isTopLevel']) {
            $this->unlinkFromCatalogues($id);
        }

        $this->clearElementCaches($id, $place['parentId'], $newParent->getId());
    }

    /**
     * The element the category hangs under, and whether that element is the
     * tree container rather than another category.
     *
     * @return array{parentId: int, isTopLevel: bool}
     * @throws CategoryException
     */
    private function getCurrentPlace(int $categoryId): array
    {
        $titles = $this->getTitles();
        foreach ($this->getTreeLinks() as $link) {
            if ($link['categoryId'] === $categoryId) {
                return [
                    'parentId' => $link['parentId'],
                    'isTopLevel' => !isset($titles[$link['parentId']]),
                ];
            }
        }

        throw new CategoryException('Category is not linked into the tree', 500);
    }

    /**
     * What a whole branch holds, filled in bottom-up for every category in it.
     *
     * A section is described by what it contains, not by the handful of
     * productions filed directly on it: "Games" carries none of its own and
     * every one of its genres' — so the tree shows both numbers, and this is the
     * second.
     *
     * @param array<int, list<int>> $childrenByParent
     * @param array<int, int> $prodCounts
     * @param array<int, int> $subtreeCounts
     * @param-out array<int, int> $subtreeCounts
     */
    private function sumSubtree(
        int $categoryId,
        array $childrenByParent,
        array $prodCounts,
        array &$subtreeCounts,
    ): int {
        $total = $prodCounts[$categoryId] ?? 0;
        foreach ($childrenByParent[$categoryId] ?? [] as $childId) {
            $total += $this->sumSubtree($childId, $childrenByParent, $prodCounts, $subtreeCounts);
        }
        $subtreeCounts[$categoryId] = $total;

        return $total;
    }

    /**
     * @param array<int, array<int, string>> $titles
     * @param array<int, list<int>> $childrenByParent
     * @param array<int, int> $prodCounts
     * @param array<int, int> $subtreeCounts
     * @param list<CategoryNodeDto> $nodes
     * @param-out list<CategoryNodeDto> $nodes
     */
    private function collectBranch(
        int $categoryId,
        ?int $parentId,
        int $level,
        array $titles,
        array $childrenByParent,
        array $prodCounts,
        array $subtreeCounts,
        int $currentLanguageId,
        array &$nodes,
    ): void {
        $categoryTitles = $titles[$categoryId] ?? [];
        $children = $childrenByParent[$categoryId] ?? [];

        $nodes[] = new CategoryNodeDto(
            id: $categoryId,
            parentId: $parentId,
            level: $level,
            title: $this->decode($categoryTitles[$currentLanguageId] ?? ''),
            titles: array_map($this->decode(...), $categoryTitles),
            prods: $prodCounts[$categoryId] ?? 0,
            prodsTotal: $subtreeCounts[$categoryId] ?? 0,
            subCategories: count($children),
        );

        foreach ($children as $childId) {
            $this->collectBranch(
                $childId,
                $categoryId,
                $level + 1,
                $titles,
                $childrenByParent,
                $prodCounts,
                $subtreeCounts,
                $currentLanguageId,
                $nodes,
            );
        }
    }

    /**
     * @throws CategoryException
     */
    private function getCategory(int $id): zxProdCategoryElement
    {
        $element = $this->structureManager->getElementById($id);

        return $element instanceof zxProdCategoryElement
            ? $element
            : throw new CategoryException('Category not found', 404);
    }

    /**
     * The element a category hangs under: another category, or the tree
     * container when it is a top-level one.
     *
     * @throws CategoryException
     */
    private function resolveParent(?int $parentId): structureElement
    {
        if ($parentId !== null) {
            return $this->getCategory($parentId);
        }

        // the tree container lives under the admin root, outside the public URL
        // tree, so it is loaded by id rather than resolved through a path
        $root = $this->structureManager->getElementByMarker(
            StructureType::ZxProdCategories->value,
            null,
            true,
        );

        return $root instanceof structureElement
            ? $root
            : throw new CategoryException('The category tree root is not available', 500);
    }

    /**
     * @param array<int, string> $titles
     * @throws CategoryException
     */
    private function applyTitles(zxProdCategoryElement $element, array $titles): void
    {
        $externalData = [];
        foreach ($titles as $languageId => $title) {
            $externalData[$languageId] = ['title' => $title];
        }

        $isValid = $element->importExternalData($externalData, ['title']);
        if ($isValid !== true) {
            throw new CategoryException('Category title is not accepted', 422);
        }

        if ($element->structureName === '') {
            $element->structureName = $this->getPrimaryTitle($titles);
        }
        $element->persistElementData();
    }

    /**
     * @return list<int> the category and everything beneath it
     */
    private function getSubtreeIds(int $categoryId): array
    {
        $childrenByParent = [];
        foreach ($this->getTreeLinks() as $link) {
            $childrenByParent[$link['parentId']][] = $link['categoryId'];
        }

        $ids = [];
        $queue = [$categoryId];
        while ($queue !== []) {
            $currentId = array_shift($queue);
            $ids[] = $currentId;
            foreach ($childrenByParent[$currentId] ?? [] as $childId) {
                $queue[] = $childId;
            }
        }

        return $ids;
    }

    /**
     * @return list<int>
     */
    private function getChildIds(int $categoryId): array
    {
        $childIds = [];
        foreach ($this->getTreeLinks() as $link) {
            if ($link['parentId'] === $categoryId) {
                $childIds[] = $link['categoryId'];
            }
        }

        return $childIds;
    }

    private function linkToCatalogues(int $categoryId): void
    {
        foreach ($this->getCatalogueFolderIds() as $folderId) {
            $this->linksManager->linkElements($folderId, $categoryId, LinkTypes::SOFT_CATALOGUE->value);
            $this->clearElementCaches($folderId);
        }
    }

    private function unlinkFromCatalogues(int $categoryId): void
    {
        foreach ($this->getCatalogueFolderIds() as $folderId) {
            $this->linksManager->unLinkElements($folderId, $categoryId, LinkTypes::SOFT_CATALOGUE->value);
            $this->clearElementCaches($folderId);
        }
    }

    /**
     * The folder of each language's software section — the element a catalogue
     * reads its top-level categories from.
     *
     * @return list<int>
     */
    private function getCatalogueFolderIds(): array
    {
        $folderIds = [];
        $catalogues = $this->structureManager->getElementsByType(
            StructureType::ZxProdCategoriesCatalogue->value,
        );
        foreach ($catalogues as $catalogue) {
            if (!$catalogue instanceof zxProdCategoriesCatalogueElement) {
                continue;
            }
            $folder = $catalogue->getFirstParentElement();
            if ($folder !== null) {
                $folderIds[] = $folder->getId();
            }
        }

        return array_values(array_unique($folderIds));
    }

    /**
     * A link written straight through the links manager leaves the parent's
     * cached copy behind — only `persistStructureLinks()` clears it — so every
     * element whose children changed is dropped from the cache here.
     */
    private function clearElementCaches(int ...$elementIds): void
    {
        foreach (array_unique($elementIds) as $elementId) {
            if ($elementId > 0) {
                $this->structureManager->clearElementCache($elementId);
            }
        }
    }

    /**
     * The title the element's own name is built from. Every language carries
     * one by then, so the first is as good as any — the structure name is only
     * ever a URL slug.
     *
     * @param array<int, string> $titles
     */
    private function getPrimaryTitle(array $titles): string
    {
        foreach ($titles as $title) {
            return $title;
        }

        return '';
    }

    /**
     * Every interface language needs a title: a category without one shows a
     * blank label to that whole audience.
     *
     * @param array<int, string> $titles
     * @return array<int, string>
     * @throws CategoryException
     */
    private function validateTitles(array $titles): array
    {
        $validated = [];
        foreach ($this->languagesManager->getLanguagesIdList() as $languageId) {
            $title = trim($titles[$languageId] ?? '');
            if ($title === '') {
                throw new CategoryException('Category title is required for language ' . $languageId, 422);
            }
            if (mb_strlen($title) > self::MAX_TITLE_LENGTH) {
                throw new CategoryException(
                    'Category title is longer than ' . self::MAX_TITLE_LENGTH . ' characters',
                    422,
                );
            }
            $validated[$languageId] = $title;
        }

        return $validated;
    }

    /**
     * @return list<array{parentId: int, categoryId: int}>
     */
    private function getTreeLinks(): array
    {
        return $this->treeLinks ??= $this->repository->getTreeLinks();
    }

    /**
     * @return array<int, array<int, string>>
     */
    private function getTitles(): array
    {
        return $this->titles ??= $this->repository->getTitles();
    }

    /**
     * @return array<int, int>
     */
    private function getProdCounts(): array
    {
        return $this->prodCounts ??= $this->repository->getProdCounts();
    }

    /**
     * A write answers with the refreshed tree in the same request, so what was
     * read before it must not be reused after it.
     */
    private function resetCache(): void
    {
        $this->treeLinks = null;
        $this->titles = null;
        $this->prodCounts = null;
    }

    private function decode(string $value): string
    {
        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
