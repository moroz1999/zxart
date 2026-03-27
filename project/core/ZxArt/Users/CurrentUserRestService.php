<?php

declare(strict_types=1);

namespace ZxArt\Users;

use App\Users\CurrentUserService;
use ZxArt\Users\Rest\CurrentUserRestDto;

class CurrentUserRestService
{
    public function __construct(
        private readonly CurrentUserService $currentUserService,
        private readonly AuthorPageUrlProvider $authorPageUrlProvider,
    ) {}

    public function buildDto(): CurrentUserRestDto
    {
        $user = $this->currentUserService->getCurrentUser();
        $userName = $user->userName ?: 'anonymous';
        $id = null;
        $authorPageUrl = null;

        if ($userName !== 'anonymous' && $user->id) {
            $id = (int)$user->id;
            if ($user->authorId !== null && $user->authorId !== '') {
                $authorPageUrl = $this->authorPageUrlProvider->getAuthorPageUrl((int)$user->authorId);
            }
        }

        return new CurrentUserRestDto(
            id: $id,
            userName: $userName,
            hasAds: $user->hasAds(),
            authorPageUrl: $authorPageUrl,
        );
    }

    public function buildAnonymousDto(): CurrentUserRestDto
    {
        return new CurrentUserRestDto(
            id: null,
            userName: 'anonymous',
            hasAds: true,
        );
    }
}
