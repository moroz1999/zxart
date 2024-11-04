<?php
declare(strict_types=1);


namespace ZxArt\Press\DataUpdater;

use authorAliasElement;
use authorElement;
use ZxArt\Labels\Label;

final readonly class ResolvedAuthorship
{
    public function __construct(
        public Label                            $label,
        public authorElement|authorAliasElement $author,
        public array                            $roles,
    )
    {

    }
}