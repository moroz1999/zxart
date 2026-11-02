<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Prods;

use Override;
use ZxArt\Assistant\Tools\Dto\ChangeKind;
use ZxArt\Assistant\Tools\Dto\ChangePreviewDto;
use ZxArt\Assistant\Tools\Dto\ChangeTargetDto;
use ZxArt\Assistant\Tools\Dto\EntityIdRequestDto;
use ZxArt\Assistant\Tools\WriteTool;
use ZxArt\Prods\Dto\ProdDataResultDto;
use ZxArt\Prods\Services\ProdStateService;
use ZxArt\Prods\UseCases\DeleteProdCommand;
use ZxArt\Prods\UseCases\DeleteProdHandler;
use ZxArt\Shared\EntityType;

/**
 * @implements WriteTool<EntityIdRequestDto>
 *
 * @psalm-api
 */
final readonly class DeleteProdTool implements WriteTool
{
    public function __construct(
        private DeleteProdHandler $deleteProdHandler,
        private ProdStateService $prodStateService,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'delete_prod';
    }

    #[Override]
    public function description(): string
    {
        return 'Deletes a production together with its releases and files.';
    }

    #[Override]
    public function requestClass(): string
    {
        return EntityIdRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        $state = $this->prodStateService->get($request->id);
        $releases = count($state->releaseIds);

        return new ChangePreviewDto(
            kind: ChangeKind::Delete,
            target: new ChangeTargetDto(EntityType::Prod, $request->id, (string)$state->fields->title),
            changes: [],
            consequences: match ($releases) {
                0 => [],
                1 => ['1 release is deleted with it'],
                default => ["$releases releases are deleted with it"],
            },
            modifiedAt: $state->modifiedAt,
        );
    }

    #[Override]
    public function execute(object $request): ProdDataResultDto
    {
        return $this->deleteProdHandler->handle($this->command($request));
    }

    private function command(EntityIdRequestDto $request): DeleteProdCommand
    {
        return new DeleteProdCommand($request->id);
    }
}
