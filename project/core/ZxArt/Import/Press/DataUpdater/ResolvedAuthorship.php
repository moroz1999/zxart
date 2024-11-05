<?php
declare(strict_types=1);


namespace ZxArt\Import\Press\DataUpdater;

use authorAliasElement;
use authorElement;
use ZxArt\Import\Labels\Label;

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