<?php
declare(strict_types=1);


namespace ZxArt\Import\Press\DataUpdater;

use groupAliasElement;
use groupElement;
use ZxArt\Import\Labels\Label;

final readonly class ResolvedGroup
{
    public function __construct(
        public Label                          $label,
        public groupElement|groupAliasElement $group,
    )
    {
    }
}