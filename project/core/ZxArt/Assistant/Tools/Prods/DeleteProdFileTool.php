<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Prods;

use Override;
use ZxArt\Assistant\Tools\Dto\ChangeKind;
use ZxArt\Assistant\Tools\Dto\ChangePreviewDto;
use ZxArt\Assistant\Tools\Dto\ChangeTargetDto;
use ZxArt\Assistant\Tools\Dto\FieldChangeDto;
use ZxArt\Assistant\Tools\WriteTool;
use ZxArt\Prods\Dto\ProdDataResultDto;
use ZxArt\Prods\Services\ProdStateService;
use ZxArt\Prods\UseCases\DeleteProdFileCommand;
use ZxArt\Prods\UseCases\DeleteProdFileHandler;
use ZxArt\Shared\Dto\AttachedFileDto;
use ZxArt\Shared\EntityType;

/**
 * @implements WriteTool<DeleteProdFileRequestDto>
 *
 * @psalm-api
 */
final readonly class DeleteProdFileTool implements WriteTool
{
    public function __construct(
        private DeleteProdFileHandler $deleteProdFileHandler,
        private ProdStateService $prodStateService,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'delete_prod_file';
    }

    #[Override]
    public function description(): string
    {
        return 'Deletes a file from the file lists of a production.';
    }

    #[Override]
    public function requestClass(): string
    {
        return DeleteProdFileRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        $state = $this->prodStateService->get($request->prodId);
        $changes = [];
        foreach ($state->files as $kind => $files) {
            $names = array_map(static fn(AttachedFileDto $file): string => $file->fileName, $files);
            $left = array_map(
                static fn(AttachedFileDto $file): string => $file->fileName,
                array_values(array_filter($files, static fn(AttachedFileDto $file): bool => $file->id !== $request->fileId)),
            );
            if ($left !== $names) {
                $changes[] = new FieldChangeDto($kind, $names, $left);
            }
        }

        return new ChangePreviewDto(
            kind: ChangeKind::DeleteFile,
            target: new ChangeTargetDto(EntityType::Prod, $request->prodId, (string)$state->fields->title),
            changes: $changes,
            consequences: [],
            modifiedAt: $state->modifiedAt,
        );
    }

    #[Override]
    public function execute(object $request): ProdDataResultDto
    {
        return $this->deleteProdFileHandler->handle($this->command($request));
    }

    private function command(DeleteProdFileRequestDto $request): DeleteProdFileCommand
    {
        return new DeleteProdFileCommand($request->prodId, $request->fileId);
    }
}
