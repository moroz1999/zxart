<?php

declare(strict_types=1);

namespace ZxArt\Users;

use App\Users\CurrentUserService;
use structureManager;
use ZxArt\Users\Rest\CurrentUserRestDto;

class CurrentUserRestService
{
    public function __construct(
        private readonly CurrentUserService $currentUserService,
        private readonly structureManager $structureManager,
    ) {}

    public function buildDto(): CurrentUserRestDto
    {
        $user = $this->currentUserService->getCurrentUser();
        $userName = $user->userName ?: 'anonymous';
        $id = null;
        $authorId = null;

        if ($userName !== 'anonymous' && $user->id) {
            $id = (int)$user->id;
            if ($user->authorId !== null && $user->authorId !== '') {
                $authorId = (int)$user->authorId;
            }
        }

        return new CurrentUserRestDto(
            id: $id,
            userName: $userName,
            publicRootId: $this->structureManager->getRootElementId(),
            authorId: $authorId,
        );
    }

    public function buildAnonymousDto(): CurrentUserRestDto
    {
        return new CurrentUserRestDto(
            id: null,
            userName: 'anonymous',
            publicRootId: $this->structureManager->getRootElementId(),
        );
    }
}
