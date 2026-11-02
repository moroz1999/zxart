<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Prods;

use Override;
use ZxArt\Assistant\Tools\Dto\ChangeKind;
use ZxArt\Assistant\Tools\Dto\ChangePreviewDto;
use ZxArt\Assistant\Tools\Dto\ChangeTargetDto;
use ZxArt\Assistant\Tools\Previews\FieldChanges;
use ZxArt\Assistant\Tools\WriteTool;
use ZxArt\Prods\Dto\ProdDataResultDto;
use ZxArt\Prods\Services\ProdStateService;
use ZxArt\Prods\UseCases\UpdateProdCommand;
use ZxArt\Prods\UseCases\UpdateProdHandler;
use ZxArt\Shared\EntityType;

/**
 * @implements WriteTool<UpdateProdRequestDto>
 *
 * @psalm-api
 */
final readonly class UpdateProdTool implements WriteTool
{
    public function __construct(
        private UpdateProdHandler $updateProdHandler,
        private ProdStateService $prodStateService,
        private FieldChanges $fieldChanges,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'update_prod';
    }

    #[Override]
    public function description(): string
    {
        return 'Changes the fields of a production that are given and leaves the others; a list given replaces '
            . 'the whole list, an empty string or list clears the field.';
    }

    #[Override]
    public function requestClass(): string
    {
        return UpdateProdRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        $state = $this->prodStateService->get($request->id);

        return new ChangePreviewDto(
            kind: ChangeKind::Update,
            target: new ChangeTargetDto(EntityType::Prod, $request->id, (string)$state->fields->title),
            changes: $this->fieldChanges->diff($state->fields, $request->fields),
            consequences: [],
            modifiedAt: $state->modifiedAt,
        );
    }

    #[Override]
    public function execute(object $request): ProdDataResultDto
    {
        return $this->updateProdHandler->handle($this->command($request));
    }

    private function command(UpdateProdRequestDto $request): UpdateProdCommand
    {
        return new UpdateProdCommand($request->id, $request->fields);
    }
}
