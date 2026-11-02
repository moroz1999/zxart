<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Prods;

use Override;
use ZxArt\Assistant\Tools\Dto\ChangeKind;
use ZxArt\Assistant\Tools\Dto\ChangePreviewDto;
use ZxArt\Assistant\Tools\Dto\ChangeTargetDto;
use ZxArt\Assistant\Tools\WriteTool;
use ZxArt\Prods\Dto\ProdDataResultDto;
use ZxArt\Prods\Services\ProdStateService;
use ZxArt\Prods\UseCases\JoinProdsCommand;
use ZxArt\Prods\UseCases\JoinProdsHandler;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityMentionDto;

/**
 * @implements WriteTool<JoinProdsRequestDto>
 *
 * @psalm-api
 */
final readonly class JoinProdsTool implements WriteTool
{
    public function __construct(
        private JoinProdsHandler $joinProdsHandler,
        private ProdStateService $prodStateService,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'join_prods';
    }

    #[Override]
    public function description(): string
    {
        return 'Merges a duplicate production into the main one: its releases and materials move, and unless '
            . 'releasesOnly also its authors, groups, publishers, categories and the fields the main one lacks; '
            . 'the joined production is deleted.';
    }

    #[Override]
    public function requestClass(): string
    {
        return JoinProdsRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        $main = $this->prodStateService->get($request->mainId);
        $joined = $this->prodStateService->get($request->joinedId);
        $mainText = (new EntityMentionDto(EntityType::Prod, $request->mainId))->text();
        $joinedText = (new EntityMentionDto(EntityType::Prod, $request->joinedId))->text();
        $releases = count($joined->releaseIds);

        $consequences = ["$joinedText is deleted"];
        if ($releases > 0) {
            $consequences[] = $releases === 1 ? "1 release moves to $mainText" : "$releases releases move to $mainText";
        }
        if (!$request->releasesOnly) {
            $consequences[] = "The authors, groups, publishers, categories and the fields $mainText lacks move from $joinedText";
        }

        return new ChangePreviewDto(
            kind: ChangeKind::Join,
            target: new ChangeTargetDto(EntityType::Prod, $request->mainId, (string)$main->fields->title),
            changes: [],
            consequences: $consequences,
            modifiedAt: $main->modifiedAt,
        );
    }

    #[Override]
    public function execute(object $request): ProdDataResultDto
    {
        return $this->joinProdsHandler->handle($this->command($request));
    }

    private function command(JoinProdsRequestDto $request): JoinProdsCommand
    {
        return new JoinProdsCommand($request->mainId, $request->joinedId, $request->releasesOnly);
    }
}
