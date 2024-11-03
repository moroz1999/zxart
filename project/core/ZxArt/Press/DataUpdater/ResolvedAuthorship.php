<?php
declare(strict_types=1);


namespace ZxArt\Press\DataUpdater;

use \structureElement;
use ZxArt\Labels\Label;

final readonly class ResolvedAuthorship
{
    public function __construct(
        public Label            $label,
        public structureElement $author,
        public array            $roles,
    )
    {

    }
}