<?php

declare(strict_types=1);

namespace ZxArt\Tests\Doubles\Elements;

require_once __DIR__ . '/ElementSpyTrait.php';

use Override;
use authorAliasElement;

/**
 * An author alias without the CMS data layer, see {@see ElementSpyTrait}.
 */
final class AuthorAliasElementSpy extends authorAliasElement
{
    use ElementSpyTrait;

    public function __construct(
        private readonly int $elementId = 42,
        private readonly int $createdTimestamp = 1700000000,
    ) {
        parent::__construct('');
    }

    #[Override]
    public function checkParentLetter(): void
    {
        $this->calls[] = 'checkParentLetter';
    }
}
