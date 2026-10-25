<?php

declare(strict_types=1);

namespace ZxArt\Tests\Doubles\Elements;

require_once __DIR__ . '/ElementSpyTrait.php';

use Override;
use zxReleaseElement;

/**
 * A release without the CMS data layer, see {@see ElementSpyTrait}.
 */
final class ZxReleaseElementSpy extends zxReleaseElement
{
    use ElementSpyTrait;

    public function __construct(
        private readonly int $elementId = 42,
        private readonly int $createdTimestamp = 1700000000,
    ) {
        parent::__construct('');
    }

    #[Override]
    public function updateFileStructure(): void
    {
        $this->calls[] = 'updateFileStructure';
    }
}
