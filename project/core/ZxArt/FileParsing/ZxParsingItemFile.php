<?php

namespace ZxArt\FileParsing;

class ZxParsingItemFile extends ZxParsingItem
{
    #[Override] public function getType(): string
    {
        return 'file';
    }

    #[Override] protected function parse(): void
    {
    }
}
