<?php
declare(strict_types=1);


namespace ZxArt\Press\DataUpdater;

use groupAliasElement;
use groupElement;
use ZxArt\Labels\Label;

final readonly class ResolvedGroup
{
    public function __construct(
        public Label                          $label,
        public groupElement|groupAliasElement $group,
    )
    {
    }
}