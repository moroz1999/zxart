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
use ZxArt\Prods\UseCases\CreateProdCommand;
use ZxArt\Prods\UseCases\CreateProdHandler;
use ZxArt\Shared\EntityType;

/**
 * @implements WriteTool<CreateProdRequestDto>
 *
 * @psalm-api
 */
final readonly class CreateProdTool implements WriteTool
{
    public function __construct(
        private CreateProdHandler $createProdHandler,
        private FieldChanges $fieldChanges,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'create_prod';
    }

    #[Override]
    public function description(): string
    {
        return 'Creates a production with the fields given; it is placed under its first category, or Misc. '
            . 'Credit authors with set_prod_author and add releases with create_release afterwards.';
    }

    #[Override]
    public function requestClass(): string
    {
        return CreateProdRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        return new ChangePreviewDto(
            kind: ChangeKind::Create,
            target: new ChangeTargetDto(EntityType::Prod, null, (string)$request->fields->title),
            changes: $this->fieldChanges->diff(null, $request->fields),
            consequences: [],
            modifiedAt: null,
        );
    }

    #[Override]
    public function execute(object $request): ProdDataResultDto
    {
        return $this->createProdHandler->handle($this->command($request));
    }

    private function command(CreateProdRequestDto $request): CreateProdCommand
    {
        return new CreateProdCommand($request->fields);
    }
}
