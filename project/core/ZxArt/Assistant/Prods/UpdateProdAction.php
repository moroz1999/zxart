<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Prods;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Prods\Dto\ProdDataResultDto;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\UseCases\UpdateProdCommand;
use ZxArt\Prods\UseCases\UpdateProdHandler;
use ZxArt\Shared\EntityType;

/**
 * Changes the given fields of a production.
 *
 * @psalm-api
 */
final readonly class UpdateProdAction implements WriteAction
{
    public function __construct(
        private ProdDtos $prodDtos,
        private UpdateProdHandler $updateProdHandler,
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
        return 'Changes the fields of a production that are given and leaves the others; a list given replaces the whole '
            . 'list, an empty string or list clears the field.';
    }

    #[Override]
    public function schema(): string
    {
        return UpdateProdArgumentsDto::SCHEMA;
    }

    #[Override]
    public function kind(): ChangeKind
    {
        return ChangeKind::Update;
    }

    #[Override]
    public function entityType(): EntityType
    {
        return EntityType::Prod;
    }

    #[Override]
    public function state(Arguments $arguments): ProdDto
    {
        return $this->prodDtos->prod(UpdateProdArgumentsDto::from($arguments)->id);
    }

    #[Override]
    public function write(Arguments $arguments): ProdDataResultDto
    {
        $argumentsDto = UpdateProdArgumentsDto::from($arguments);

        return $this->updateProdHandler->handle(new UpdateProdCommand($argumentsDto->id, new ProdFieldsDto(
            title: $argumentsDto->title,
            altTitle: $argumentsDto->altTitle,
            year: $argumentsDto->year,
            description: $argumentsDto->description,
            instructions: $argumentsDto->instructions,
            legalStatus: $argumentsDto->legalStatus,
            externalLink: $argumentsDto->externalLink,
            youtubeId: $argumentsDto->youtubeId,
            partyId: $argumentsDto->partyId,
            partyPlace: $argumentsDto->partyPlace,
            compo: $argumentsDto->compo,
            languages: $argumentsDto->languages,
            hardware: $argumentsDto->hardware,
            categoryIds: $argumentsDto->categoryIds,
            groupIds: $argumentsDto->groupIds,
            publisherIds: $argumentsDto->publisherIds,
            tags: $argumentsDto->tags,
            compilationItemIds: $argumentsDto->compilationItemIds,
            seriesProdIds: $argumentsDto->seriesProdIds,
        )));
    }
}
