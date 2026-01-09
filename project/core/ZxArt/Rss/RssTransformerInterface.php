<?php
declare(strict_types=1);

namespace ZxArt\Rss;

use structureElement;

interface RssTransformerInterface
{
    public function transform(structureElement $element): RssDto;
}
