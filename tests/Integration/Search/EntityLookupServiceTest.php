<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Search;

require_once __DIR__ . '/../Entities/EntityWriteTestCase.php';

use LanguagesManager;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ZxArt\Authors\Dto\AuthorAliasFieldsDto;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\UseCases\CreateAuthorAliasCommand;
use ZxArt\Authors\UseCases\CreateAuthorAliasHandler;
use ZxArt\Authors\UseCases\CreateAuthorCommand;
use ZxArt\Authors\UseCases\CreateAuthorHandler;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Groups\UseCases\CreateGroupCommand;
use ZxArt\Groups\UseCases\CreateGroupHandler;
use ZxArt\Search\Dto\EntityLookupItemDto;
use ZxArt\Search\Services\EntityLookupService;
use ZxArt\Search\Services\PhraseSearchType;
use ZxArt\Tests\Integration\Entities\EntityWriteTestCase;

/**
 * What a relation picker is offered for a typed phrase: the entities of the
 * picker's types, each named the way it is told apart from the others.
 */
#[RunTestsInSeparateProcesses]
final class EntityLookupServiceTest extends EntityWriteTestCase
{
    private const int COUNTRY_POLAND = 47450;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS]);
    }

    public function testAnAuthorAndItsAliasAreOfferedTheAliasNamedWithItsAuthor(): void
    {
        $authorId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: 'Qzxlook Writer')))->id;
        $aliasId = $this->container->get(CreateAuthorAliasHandler::class)->handle(new CreateAuthorAliasCommand(
            new AuthorAliasFieldsDto(title: 'Qzxlook Wanderer', authorId: $authorId),
        ))->id;

        self::assertEquals(
            [new EntityLookupItemDto($authorId, 'Qzxlook Writer'), new EntityLookupItemDto($aliasId, 'Qzxlook Wanderer (Qzxlook Writer)')],
            $this->lookup('qzx-look', [PhraseSearchType::Author, PhraseSearchType::AuthorAlias]),
        );
    }

    public function testOnlyThePickersTypesAreOffered(): void
    {
        $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: 'Qzxlook Writer')));
        $groupId = $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand(new GroupFieldsDto(title: 'Qzxlook Crew')))->id;

        self::assertEquals([new EntityLookupItemDto($groupId, 'Qzxlook Crew')], $this->lookup('qzxlook', [PhraseSearchType::Group]));
    }

    public function testACountryIsOfferedByItsName(): void
    {
        $ids = array_map(static fn(EntityLookupItemDto $item): int => $item->id, $this->lookup('Pol-and', [PhraseSearchType::Country]));

        self::assertContains(self::COUNTRY_POLAND, $ids);
    }

    public function testABlankPhraseOffersNothing(): void
    {
        self::assertSame([], $this->lookup(' ', [PhraseSearchType::Author]));
    }

    /**
     * @param PhraseSearchType[] $types
     * @return EntityLookupItemDto[]
     */
    private function lookup(string $phrase, array $types): array
    {
        $languageId = (int)$this->container->get(LanguagesManager::class)->getCurrentLanguageId();

        return $this->container->get(EntityLookupService::class)->lookup($phrase, $types, $languageId);
    }
}
