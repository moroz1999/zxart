<?php

declare(strict_types=1);

namespace ZxArt\Tests\Doubles\Elements;

use tagElement;

final class TagElementStub extends tagElement
{
    public function __construct(
        public int $id,
        public string $title,
    ) {
        parent::__construct('');
    }
}
