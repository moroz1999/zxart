<?php

namespace ZxArt\Elements;

use pressArticleElement;

interface PressMentionsProvider
{
    /**
     * @return pressArticleElement[]
     */
    public function getPressMentions(): array;
}