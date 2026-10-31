<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Prods;

use Override;
use ZxArt\Assistant\Tools\Dto\ChangeKind;
use ZxArt\Assistant\Tools\Dto\ChangePreviewDto;
use ZxArt\Assistant\Tools\Dto\ChangeTargetDto;
use ZxArt\Assistant\Tools\Previews\CreditChanges;
use ZxArt\Assistant\Tools\WriteTool;
use ZxArt\Prods\Dto\ProdDataResultDto;
use ZxArt\Prods\Services\ProdStateService;
use ZxArt\Prods\UseCases\DeleteProdMemberCommand;
use ZxArt\Prods\UseCases\DeleteProdMemberHandler;
use ZxArt\Shared\EntityType;

/**
 * @implements WriteTool<RemoveProdAuthorRequestDto>
 *
 * @psalm-api
 */
final readonly class RemoveProdAuthorTool implements WriteTool
{
    public function __construct(
        private DeleteProdMemberHandler $deleteProdMemberHandler,
        private ProdStateService $prodStateService,
        private CreditChanges $creditChanges,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'remove_prod_author';
    }

    #[Override]
    public function description(): string
    {
        return 'Removes the credit of an author or an author alias from a production.';
    }

    #[Override]
    public function requestClass(): string
    {
        return RemoveProdAuthorRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        $this->deleteProdMemberHandler->check($this->command($request));
        $state = $this->prodStateService->get($request->prodId);

        return new ChangePreviewDto(
            kind: ChangeKind::Unlink,
            target: new ChangeTargetDto(EntityType::Prod, $request->prodId, (string)$state->fields->title),
            changes: $this->creditChanges->remove($state->authors, $request->authorId),
            consequences: [],
            modifiedAt: $state->modifiedAt,
        );
    }

    #[Override]
    public function execute(object $request): ProdDataResultDto
    {
        return $this->deleteProdMemberHandler->handle($this->command($request));
    }

    private function command(RemoveProdAuthorRequestDto $request): DeleteProdMemberCommand
    {
        return new DeleteProdMemberCommand($request->prodId, $request->authorId);
    }
}
