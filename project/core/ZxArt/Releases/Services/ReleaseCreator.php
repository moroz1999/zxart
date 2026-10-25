<?php

declare(strict_types=1);

namespace ZxArt\Releases\Services;

use App\Users\CurrentUserService;
use ZxArt\Releases\Dto\ReleaseFieldsDto;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Releases\Repositories\ReleaseRepository;
use ZxArt\Shared\Elements\CreatorPrivilegesGranter;
use zxReleaseElement;

/**
 * Creates a release on behalf of the current user: the element under its
 * production, its fields, and the privileges that let its creator keep editing
 * it. Checks no privilege of its own — the caller does, and refreshes the user's
 * privileges once it is done creating.
 *
 * @psalm-api
 */
final readonly class ReleaseCreator
{
    /** @var list<array{string, string}> structure type, action */
    private const array CREATOR_PRIVILEGES = [
        ['zxRelease', 'showPublicForm'],
        ['zxRelease', 'publicReceive'],
        ['zxRelease', 'publicDelete'],
        ['zxRelease', 'deleteFile'],
        ['zxRelease', 'clone'],
    ];

    public function __construct(
        private ReleaseRepository $releaseRepository,
        private ReleaseFieldsWriter $releaseFieldsWriter,
        private CreatorPrivilegesGranter $creatorPrivilegesGranter,
        private CurrentUserService $currentUserService,
    ) {
    }

    /**
     * @throws ReleaseDataException
     */
    public function create(ReleaseFieldsDto $fields): zxReleaseElement
    {
        $release = $this->releaseRepository->create($fields->prodId ?? 0);
        $this->completeCreation($release, $fields);

        return $release;
    }

    /**
     * Fills and grants a release element that was created but not yet written —
     * by {@see create()}, or by the release form, which creates its own.
     */
    public function completeCreation(zxReleaseElement $release, ReleaseFieldsDto $fields): void
    {
        $userId = (int)$this->currentUserService->getCurrentUser()->id;
        // persisting the structure record is what gives the element its creation time
        $releaseId = $release->getPersistedId();
        $release->dateAdded = $release->getCreatedTimestamp();
        $release->userId = $userId;

        $this->releaseFieldsWriter->apply($release, $fields);
        $this->creatorPrivilegesGranter->grant($releaseId, self::CREATOR_PRIVILEGES);
    }
}
