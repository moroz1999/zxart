<?php

declare(strict_types=1);

namespace ZxArt\Tests\Doubles\Elements;

require_once __DIR__ . '/ElementSpyTrait.php';

use Override;
use zxProdElement;

/**
 * A production without the CMS data layer, see {@see ElementSpyTrait}.
 */
final class ZxProdElementSpy extends zxProdElement
{
    use ElementSpyTrait;

    /** @var string[]|null */
    public ?array $tagTitles = null;

    /**
     * @param int[] $connectedCategoryIds
     */
    public function __construct(
        private readonly int $elementId = 42,
        private readonly array $connectedCategoryIds = [],
        private readonly int $createdTimestamp = 1700000000,
    ) {
        parent::__construct('');
    }

    #[Override]
    public function getConnectedCategoriesIds()
    {
        return $this->connectedCategoryIds;
    }

    #[Override]
    public function renewPartyLink(): void
    {
        $this->calls[] = 'renewPartyLink';
    }

    #[Override]
    public function updateTagsFromList(array $tagNames): void
    {
        $this->calls[] = 'updateTagsFromList';
        $this->tagTitles = $tagNames;
    }

    #[Override]
    public function getTagsTexts(): array
    {
        return $this->tagTitles ?? [];
    }

    #[Override]
    public function updateYear(): void
    {
        $this->calls[] = 'updateYear';
    }

    #[Override]
    public function checkAndPersistCategories()
    {
        $this->calls[] = 'checkAndPersistCategories';
    }

    #[Override]
    public function logCreation($userId = null): void
    {
        $this->calls[] = 'logCreation';
    }
}
