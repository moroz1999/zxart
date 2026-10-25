<?php

declare(strict_types=1);

namespace ZxArt\Tests\Doubles\Elements;

require_once __DIR__ . '/ElementSpyTrait.php';

use Override;
use groupElement;

/**
 * A group without the CMS data layer, see {@see ElementSpyTrait}.
 */
final class GroupElementSpy extends groupElement
{
    use ElementSpyTrait;

    /**
     * @param int[] $linkedSubGroupIds
     */
    public function __construct(
        private readonly int $elementId = 42,
        private readonly int $createdTimestamp = 1700000000,
        private readonly array $linkedSubGroupIds = [],
    ) {
        parent::__construct('');
    }

    #[Override]
    public function getSubGroupIds()
    {
        return $this->linkedSubGroupIds;
    }

    #[Override]
    public function checkParentLetter(): void
    {
        $this->calls[] = 'checkParentLetter';
    }

    #[Override]
    public function recalculate(): void
    {
        $this->calls[] = 'recalculate';
    }

    #[Override]
    public function persistSubGroupConnections(): void
    {
        $this->calls[] = 'persistSubGroupConnections';
    }
}
