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
use ZxArt\Prods\UseCases\SetProdMemberCommand;
use ZxArt\Prods\UseCases\SetProdMemberHandler;
use ZxArt\Shared\EntityType;

/**
 * @implements WriteTool<SetProdAuthorRequestDto>
 *
 * @psalm-api
 */
final readonly class SetProdAuthorTool implements WriteTool
{
    public function __construct(
        private SetProdMemberHandler $setProdMemberHandler,
        private ProdStateService $prodStateService,
        private CreditChanges $creditChanges,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'set_prod_author';
    }

    #[Override]
    public function description(): string
    {
        return 'Credits an author or an author alias on a production with the roles given, or replaces the roles '
            . 'of one already credited.';
    }

    #[Override]
    public function requestClass(): string
    {
        return SetProdAuthorRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        $state = $this->prodStateService->get($request->prodId);

        return new ChangePreviewDto(
            kind: ChangeKind::Link,
            target: new ChangeTargetDto(EntityType::Prod, $request->prodId, (string)$state->fields->title),
            changes: $this->creditChanges->set($state->authors, $request->authorId, $request->roles),
            consequences: [],
            modifiedAt: $state->modifiedAt,
        );
    }

    #[Override]
    public function execute(object $request): ProdDataResultDto
    {
        return $this->setProdMemberHandler->handle($this->command($request));
    }

    private function command(SetProdAuthorRequestDto $request): SetProdMemberCommand
    {
        return new SetProdMemberCommand($request->prodId, $request->authorId, $request->roles);
    }
}
