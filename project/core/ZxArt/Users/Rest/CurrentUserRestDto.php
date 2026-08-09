<?php

declare(strict_types=1);

namespace ZxArt\Users\Rest;

readonly class CurrentUserRestDto
{
    /**
     * @param int $publicRootId the element site-wide privileges are held on, so
     *        the SPA can ask `/element-privileges/` about them without
     *        hardcoding an id
     */
    public function __construct(
        public ?int $id,
        public string $userName,
        public int $publicRootId,
        public ?int $authorId = null,
    ) {
    }
}
