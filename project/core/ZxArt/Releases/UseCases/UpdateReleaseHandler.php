<?php

declare(strict_types=1);

namespace ZxArt\Releases\UseCases;

use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Releases\Dto\ReleaseDataResultDto;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Releases\Repositories\ReleaseRepository;
use ZxArt\Releases\Services\ReleaseFieldsWriter;
use ZxArt\Releases\Services\ReleasePrivileges;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * Moving the release to another production needs the right to add releases
 * there.
 *
 * @psalm-api
 */
final readonly class UpdateReleaseHandler
{
    private const string PRIVILEGE = 'publicReceive';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private ReleaseRepository $releaseRepository,
        private ReleasePrivileges $releasePrivileges,
        private ReleaseFieldsWriter $releaseFieldsWriter,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws ReleaseDataException
     */
    public function handle(UpdateReleaseCommand $command): ReleaseDataResultDto
    {
        $release = $this->releaseRepository->get($command->releaseId);
        $this->releasePrivileges->assert($command->releaseId, self::PRIVILEGE);
        $prodId = $command->fields->prodId;
        if ($prodId !== null && $prodId !== $release->getProd()?->getId()) {
            $this->releasePrivileges->assertMayAddTo($prodId);
        }

        $this->transactionRunner->run(function () use ($release, $command): void {
            $this->releaseFieldsWriter->apply($release, $command->fields);
            $this->actionsLogService->log($release, StructureType::ZxRelease, self::PRIVILEGE);
        });

        return new ReleaseDataResultDto(id: $command->releaseId);
    }
}
