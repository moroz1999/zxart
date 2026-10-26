<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Tools\Catalogues;

use PHPUnit\Framework\TestCase;
use ZxArt\Assistant\Tools\Catalogues\ListOptionsRequestDto;
use ZxArt\Assistant\Tools\Catalogues\ListOptionsTool;
use ZxArt\Authors\AuthorshipRoles;
use ZxArt\Groups\GroupMemberRoles;
use ZxArt\Groups\GroupTypes;
use ZxArt\Prods\LegalStatus;
use ZxArt\Releases\ReleaseTypes;
use ZxArt\Releases\Services\ReleaseFormatsProvider;
use ZxArt\Shared\LanguageCodes;

final class ListOptionsToolTest extends TestCase
{
    public function testItIsNamedListOptions(): void
    {
        $tool = new ListOptionsTool($this->createStub(ReleaseFormatsProvider::class));

        self::assertSame('list_options', $tool->name());
        self::assertSame(ListOptionsRequestDto::class, $tool->requestClass());
    }

    public function testItAnswersTheFixedValueListsTheWritesAndSearchesAccept(): void
    {
        $formats = $this->createStub(ReleaseFormatsProvider::class);
        $formats->method('getReleaseFormats')->willReturn(['tap', 'trd']);

        $options = (new ListOptionsTool($formats))->execute(new ListOptionsRequestDto());

        self::assertSame(array_map(static fn(LegalStatus $status): string => $status->value, LegalStatus::cases()), $options->legalStatuses);
        self::assertSame(ReleaseTypes::getAllValues(), $options->releaseTypes);
        self::assertSame(['tap', 'trd'], $options->releaseFormats);
        self::assertSame(LanguageCodes::ALL, $options->languages);
        self::assertSame(GroupTypes::LIST, $options->groupTypes);
        self::assertSame(AuthorshipRoles::LIST, $options->authorRoles);
        self::assertSame(GroupMemberRoles::LIST, $options->memberRoles);
    }
}
