<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Prods;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Prods\Dto\ProdDataResultDto;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\UseCases\CreateProdCommand;
use ZxArt\Prods\UseCases\CreateProdHandler;
use ZxArt\Shared\EntityType;

/**
 * Creates a production.
 *
 * @psalm-api
 */
final readonly class CreateProdAction implements WriteAction
{
    public function __construct(
        private CreateProdHandler $createProdHandler,
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
        return 'Creates a production with the fields given; it is placed under its first category, or Misc. Credit its '
            . 'authors with set_prod_author afterwards.';
    }

    #[Override]
    public function schema(): string
    {
        return CreateProdArgumentsDto::SCHEMA;
    }

    #[Override]
    public function kind(): ChangeKind
    {
        return ChangeKind::Create;
    }

    #[Override]
    public function entityType(): EntityType
    {
        return EntityType::Prod;
    }

    #[Override]
    public function state(Arguments $arguments): null
    {
        return null;
    }

    #[Override]
    public function write(Arguments $arguments): ProdDataResultDto
    {
        $argumentsDto = CreateProdArgumentsDto::from($arguments);

        return $this->createProdHandler->handle(new CreateProdCommand(new ProdFieldsDto(
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
