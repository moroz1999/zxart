<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

readonly class ProdPrivilegesDto
{
    public function __construct(
        public bool $showPublicForm,
        public bool $showAiForm,
        public bool $resize,
        public bool $join,
        public bool $split,
        public bool $publicDelete,
        public bool $addRelease,
        public bool $addPressArticle,
    ) {
    }
}
