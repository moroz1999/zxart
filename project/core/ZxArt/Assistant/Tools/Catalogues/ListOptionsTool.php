<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Catalogues;

use Override;
use ZxArt\Assistant\Tools\ReadTool;
use ZxArt\Authors\AuthorshipRoles;
use ZxArt\Groups\GroupMemberRoles;
use ZxArt\Groups\GroupTypes;
use ZxArt\Prods\LegalStatus;
use ZxArt\Releases\ReleaseTypes;
use ZxArt\Releases\Services\ReleaseFormatsProvider;
use ZxArt\Shared\LanguageCodes;

/**
 * @implements ReadTool<ListOptionsRequestDto>
 *
 * @psalm-api
 */
final readonly class ListOptionsTool implements ReadTool
{
    public function __construct(
        private ReleaseFormatsProvider $releaseFormatsProvider,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'list_options';
    }

    #[Override]
    public function description(): string
    {
        return 'Lists the fixed values the searches and edits accept: legal statuses, release types, release '
            . 'formats, language codes, group types, the roles of authors in productions and releases, and the '
            . 'roles of group members.';
    }

    #[Override]
    public function requestClass(): string
    {
        return ListOptionsRequestDto::class;
    }

    #[Override]
    public function execute(object $request): OptionsDto
    {
        return new OptionsDto(
            legalStatuses: array_map(static fn(LegalStatus $status): string => $status->value, LegalStatus::cases()),
            releaseTypes: ReleaseTypes::getAllValues(),
            releaseFormats: array_values($this->releaseFormatsProvider->getReleaseFormats()),
            languages: LanguageCodes::ALL,
            groupTypes: GroupTypes::LIST,
            authorRoles: AuthorshipRoles::LIST,
            memberRoles: GroupMemberRoles::LIST,
        );
    }
}
