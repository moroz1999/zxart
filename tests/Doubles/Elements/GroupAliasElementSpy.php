<?php

declare(strict_types=1);

namespace ZxArt\Tests\Doubles\Elements;

require_once __DIR__ . '/ElementSpyTrait.php';

use groupAliasElement;

/**
 * A group alias without the CMS data layer, see {@see ElementSpyTrait}.
 */
final class GroupAliasElementSpy extends groupAliasElement
{
    use ElementSpyTrait;

    public function __construct(
        private readonly int $elementId = 42,
        private readonly int $createdTimestamp = 1700000000,
    ) {
        parent::__construct('');
    }
}
