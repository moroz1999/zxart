<?php

namespace ZxArt\FileParsing;
use Override;

class ZxParsingItemFolder extends ZxParsingItem
{

    #[Override] public function getType(): string
    {
        return 'folder';
    }

    #[Override] protected function parse(): void
    {
        $this->items = [];
    }

    #[Override] public function getContent(): ?string
    {
        if (!$this->content) {
            $this->content = $this->parentMd5 . '/' . $this->itemName;
        }
        return $this->content;
    }
}
