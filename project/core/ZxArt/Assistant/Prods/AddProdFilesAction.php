<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Prods;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Prods\UseCases\AddProdFilesCommand;
use ZxArt\Prods\UseCases\AddProdFilesHandler;
use ZxArt\Shared\Dto\AddedFilesDto;
use ZxArt\Shared\EntityType;

/**
 * Downloads files into a file list of a production.
 *
 * @psalm-api
 */
final readonly class AddProdFilesAction implements WriteAction
{
    public function __construct(
        private ProdDtos $prodDtos,
        private AddProdFilesHandler $addProdFilesHandler,
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
    public function schema(): string
    {
        return AddProdFilesArgumentsDto::SCHEMA;
    }

    #[Override]
    public function kind(): ChangeKind
    {
        return ChangeKind::AttachFile;
    }

    #[Override]
    public function entityType(): EntityType
    {
        return EntityType::Prod;
    }

    #[Override]
    public function state(Arguments $arguments): ProdDto
    {
        return $this->prodDtos->prod(AddProdFilesArgumentsDto::from($arguments)->prodId);
    }

    #[Override]
    public function write(Arguments $arguments): AddedFilesDto
    {
        $argumentsDto = AddProdFilesArgumentsDto::from($arguments);

        return $this->addProdFilesHandler->handle(new AddProdFilesCommand($argumentsDto->prodId, $argumentsDto->kind, $argumentsDto->urls));
    }
}
