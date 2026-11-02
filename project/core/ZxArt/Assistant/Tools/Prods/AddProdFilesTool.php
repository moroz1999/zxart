<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Prods;

use Override;
use ZxArt\Assistant\Tools\Dto\ChangeKind;
use ZxArt\Assistant\Tools\Dto\ChangePreviewDto;
use ZxArt\Assistant\Tools\Dto\ChangeTargetDto;
use ZxArt\Assistant\Tools\Dto\FieldChangeDto;
use ZxArt\Assistant\Tools\WriteTool;
use ZxArt\Prods\Services\ProdStateService;
use ZxArt\Prods\UseCases\AddProdFilesCommand;
use ZxArt\Prods\UseCases\AddProdFilesHandler;
use ZxArt\Shared\Dto\AddedFilesDto;
use ZxArt\Shared\Dto\AttachedFileDto;
use ZxArt\Shared\EntityType;

/**
 * @implements WriteTool<AddProdFilesRequestDto>
 *
 * @psalm-api
 */
final readonly class AddProdFilesTool implements WriteTool
{
    public function __construct(
        private AddProdFilesHandler $addProdFilesHandler,
        private ProdStateService $prodStateService,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'add_prod_files';
    }

    #[Override]
    public function description(): string
    {
        return 'Downloads files from the web into a file list of a production: screenshots, inlays, maps or rzx '
            . 'recordings.';
    }

    #[Override]
    public function requestClass(): string
    {
        return AddProdFilesRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        $state = $this->prodStateService->get($request->prodId);
        $names = array_map(static fn(AttachedFileDto $file): string => $file->fileName, $state->files[$request->kind->value]);

        return new ChangePreviewDto(
            kind: ChangeKind::AttachFile,
            target: new ChangeTargetDto(EntityType::Prod, $request->prodId, (string)$state->fields->title),
            changes: [new FieldChangeDto($request->kind->value, $names, [...$names, ...$request->urls])],
            consequences: [],
            modifiedAt: $state->modifiedAt,
        );
    }

    #[Override]
    public function execute(object $request): AddedFilesDto
    {
        return $this->addProdFilesHandler->handle($this->command($request));
    }

    private function command(AddProdFilesRequestDto $request): AddProdFilesCommand
    {
        return new AddProdFilesCommand($request->prodId, $request->kind, $request->urls);
    }
}
