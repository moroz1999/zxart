<?php

declare(strict_types=1);

namespace ZxArt\Prods\Rest;

readonly class ProdPrivilegesRestDto
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
