<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Entities;

require_once __DIR__ . '/EntityWriteTestCase.php';

use Closure;
use LanguagesManager;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ZxArt\Authors\Dto\AuthorAliasFieldsDto;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\Dto\AuthorSearchFilterDto;
use ZxArt\Authors\Exception\AuthorDataException;
use ZxArt\Authors\Services\AuthorSearchService;
use ZxArt\Authors\Dto\MembershipInputDto;
use ZxArt\Authors\UseCases\CreateAuthorAliasCommand;
use ZxArt\Authors\UseCases\CreateAuthorAliasHandler;
use ZxArt\Authors\UseCases\CreateAuthorCommand;
use ZxArt\Authors\UseCases\CreateAuthorHandler;
use ZxArt\Authors\UseCases\JoinAuthorsCommand;
use ZxArt\Authors\UseCases\JoinAuthorsHandler;
use ZxArt\Authors\UseCases\UpdateAuthorCommand;
use ZxArt\Authors\UseCases\UpdateAuthorHandler;
use ZxArt\Groups\Dto\GroupAliasFieldsDto;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Groups\UseCases\CreateGroupAliasCommand;
use ZxArt\Groups\UseCases\CreateGroupAliasHandler;
use ZxArt\Groups\UseCases\CreateGroupCommand;
use ZxArt\Groups\UseCases\CreateGroupHandler;
use ZxArt\Groups\UseCases\SetGroupMemberCommand;
use ZxArt\Groups\UseCases\SetGroupMemberHandler;
use ZxArt\Import\Dto\ImportOriginDto;
use ZxArt\Import\ImportOrigin;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\Dto\ProdSearchFilterDto;
use ZxArt\Prods\ProdLink;
use ZxArt\Prods\Services\ProdSearchService;
use authorElement;
use ZxArt\Prods\UseCases\CreateProdCommand;
use ZxArt\Prods\UseCases\CreateProdHandler;
use ZxArt\Prods\UseCases\SetProdMemberCommand;
use ZxArt\Prods\UseCases\SetProdMemberHandler;

/**
 * Authors written through their use cases.
 */
#[RunTestsInSeparateProcesses]
final class AuthorWritesTest extends EntityWriteTestCase
{
    private const string TABLE = 'module_author';
    private const int COUNTRY_POLAND = 47450;
    private const int CITY_WARSAW = 47452;
    private const int COUNTRY_UNITED_KINGDOM = 47542;
    private const int CITY_LONDON = 47543;
    private const array COLUMNS = [
        'title', 'realName', 'country', 'city', 'wikiLink', 'denyVoting', 'denyComments', 'deny3a',
        'displayInMusic', 'displayInGraphics', 'chipType', 'channelsType', 'frequency', 'intFrequency',
        'palette', 'zxTunesId',
    ];
    private const array COLUMNS_BUT_REAL_NAME = [
        'title', 'country', 'city', 'wikiLink', 'denyVoting', 'denyComments', 'deny3a',
        'displayInMusic', 'displayInGraphics', 'chipType', 'channelsType', 'frequency', 'intFrequency',
        'palette', 'zxTunesId',
    ];

    public function testCreateStoresEveryFieldUnderTheLetterOfItsName(): void
    {
        $this->signInAsRegisteredUser();

        $authorId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand($this->everyField()))->id;

        self::assertSame($this->everyFieldStored(), $this->storedRow(self::TABLE, $authorId, self::COLUMNS));
        self::assertSame($this->forEveryLanguage('Paint Master Writer'), $this->storedPerLanguage(self::TABLE, $authorId, 'realName'));
        self::assertSame(['structureType' => 'letter', 'structureName' => 'g'], $this->letterOf($authorId));
        self::assertSame('goldmaster-writer', $this->structureName($authorId));
    }

    public function testCreateLetsTheCreatorKeepEditingAndIsLogged(): void
    {
        $this->signInAsRegisteredUser();

        $authorId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: 'Goldmaster Writer')))->id;

        self::assertSame(
            ['author.deleteFile', 'author.publicDelete', 'author.publicReceive', 'author.showPublicForm'],
            $this->allowedOnElement($authorId),
        );
        self::assertSame(['publicAdd'], $this->loggedActions($authorId));
    }

    public function testTheCreatorMayUpdateTheAuthorRightAway(): void
    {
        $this->signInAsRegisteredUser();
        $authorId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: 'Goldmaster Writer')))->id;

        $this->container->get(UpdateAuthorHandler::class)->handle(new UpdateAuthorCommand($authorId, new AuthorFieldsDto(wikiLink: 'https://wiki.invalid/goldmaster')));

        self::assertSame('https://wiki.invalid/goldmaster', $this->storedRow(self::TABLE, $authorId, ['wikiLink'])['wikiLink']);
    }

    public function testCreateWithoutTitleIsNamedByTheRealName(): void
    {
        $this->signInAsRegisteredUser();

        $authorId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: '', realName: 'Realname Of Goldmaster')))->id;

        self::assertSame('Realname Of Goldmaster', $this->storedRow(self::TABLE, $authorId, ['title'])['title']);
        self::assertSame(['structureType' => 'letter', 'structureName' => 'r'], $this->letterOf($authorId));
    }

    public function testCreateWithoutAnyNameIsRejected(): void
    {
        $this->signInAsRegisteredUser();

        $this->expectExceptionObject(new AuthorDataException('An author needs a name or a real name', 400));
        try {
            $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: '', realName: '')));
        } finally {
            self::assertSame([], $this->newElementIds());
        }
    }

    public function testCreateWithAnUnknownCountryWritesNothing(): void
    {
        $this->signInAsRegisteredUser();

        try {
            $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: 'Goldmaster Writer', countryId: self::CITY_WARSAW)));
            self::fail('A city given as the country must be rejected');
        } catch (AuthorDataException $exception) {
            self::assertSame(400, $exception->getStatusCode());
        }
        self::assertSame([], $this->authorRows());
        // the create runs in one transaction, so not even the structure record is left
        self::assertSame([], $this->newElementIds());
    }

    public function testCreateIsForbiddenWithoutThePrivilege(): void
    {
        $this->signIn([self::PUBLIC_VISITORS]);

        try {
            $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: 'Goldmaster Writer')));
            self::fail('Creating must need author.publicAdd');
        } catch (AuthorDataException $exception) {
            self::assertSame(403, $exception->getStatusCode());
        }
        self::assertSame([], $this->newElementIds());
    }

    public function testUpdateChangesTheGivenFieldsAndKeepsTheOthers(): void
    {
        $this->signInAsEditor();
        $authorId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand($this->everyField()))->id;

        $this->container->get(UpdateAuthorHandler::class)->handle(new UpdateAuthorCommand($authorId, new AuthorFieldsDto(
            countryId: self::COUNTRY_UNITED_KINGDOM,
            cityId: self::CITY_LONDON,
            denyVoting: false,
            displayInGraphics: true,
            palette: 'srgb',
        )));

        self::assertSame(
            array_replace($this->everyFieldStored(), ['country' => self::COUNTRY_UNITED_KINGDOM, 'city' => self::CITY_LONDON, 'denyVoting' => 0, 'displayInGraphics' => 1, 'palette' => 'srgb']),
            $this->storedRow(self::TABLE, $authorId, self::COLUMNS),
        );
        self::assertSame(['publicAdd', 'publicReceive'], $this->loggedActions($authorId));
    }

    public function testUpdateClearsWithEmptyValuesAndZero(): void
    {
        $this->signInAsEditor();
        $authorId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand($this->everyField()))->id;

        $this->container->get(UpdateAuthorHandler::class)->handle(new UpdateAuthorCommand($authorId, new AuthorFieldsDto(
            countryId: 0,
            cityId: 0,
            wikiLink: '',
            palette: '',
            zxTunesId: 0,
        )));

        self::assertSame(
            array_replace($this->everyFieldStored(), ['country' => 0, 'city' => 0, 'wikiLink' => '', 'palette' => '', 'zxTunesId' => 0]),
            $this->storedRow(self::TABLE, $authorId, self::COLUMNS),
        );
    }

    public function testRenameMovesTheAuthorToTheLetterOfTheNewName(): void
    {
        $this->signInAsEditor();
        $authorId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: 'Goldmaster Writer')))->id;

        $this->container->get(UpdateAuthorHandler::class)->handle(new UpdateAuthorCommand($authorId, new AuthorFieldsDto(title: 'Zeta Goldmaster')));

        self::assertSame('Zeta Goldmaster', $this->storedRow(self::TABLE, $authorId, ['title'])['title']);
        self::assertSame(['structureType' => 'letter', 'structureName' => 'z'], $this->letterOf($authorId));
        self::assertSame('zeta-goldmaster', $this->structureName($authorId));
    }

    public function testUpdateIsForbiddenWithoutThePrivilegeOnTheAuthor(): void
    {
        $this->signIn([self::PUBLIC_VISITORS]);
        $authorId = (int)$this->db->table(self::TABLE)->min('id');
        $stored = $this->db->table(self::TABLE)->where('id', '=', $authorId)->get();

        try {
            $this->container->get(UpdateAuthorHandler::class)->handle(new UpdateAuthorCommand($authorId, new AuthorFieldsDto(wikiLink: 'https://wiki.invalid/')));
            self::fail('Updating must need author.publicReceive');
        } catch (AuthorDataException $exception) {
            self::assertSame(403, $exception->getStatusCode());
        }
        self::assertSame($stored, $this->db->table(self::TABLE)->where('id', '=', $authorId)->get());
    }

    public function testCreateStoresTheRealNameOfEachLanguage(): void
    {
        $this->signInAsRegisteredUser();

        $authorId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand($this->everyField(), realNames: $this->realNamesPerLanguage()))->id;

        self::assertSame($this->realNamesPerLanguage(), $this->storedPerLanguage(self::TABLE, $authorId, 'realName'));
        self::assertSame($this->everyFieldStoredButRealName(), $this->storedRow(self::TABLE, $authorId, self::COLUMNS_BUT_REAL_NAME));
    }

    public function testUpdateStoresTheRealNameOfEachLanguage(): void
    {
        $this->signInAsEditor();
        $authorId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand($this->everyField()))->id;

        $this->container->get(UpdateAuthorHandler::class)->handle(new UpdateAuthorCommand($authorId, new AuthorFieldsDto(title: 'Zeta Goldmaster'), realNames: $this->realNamesPerLanguage()));

        self::assertSame($this->realNamesPerLanguage(), $this->storedPerLanguage(self::TABLE, $authorId, 'realName'));
        self::assertSame(['title' => 'Zeta Goldmaster'], $this->storedRow(self::TABLE, $authorId, ['title']));
    }

    public function testCreateStoresTheGroupsProductionsAndPortalIds(): void
    {
        $this->signInAsRegisteredUser();
        $groupId = $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand(new GroupFieldsDto(title: 'Goldmaster Guild')))->id;
        $groupAliasId = $this->container->get(CreateGroupAliasHandler::class)->handle(new CreateGroupAliasCommand(new GroupAliasFieldsDto(title: 'Goldmaster Crew', groupId: $groupId)))->id;
        $prodId = $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: 'Goldmaster Demo')))->id;

        $authorId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(
            new AuthorFieldsDto(title: 'Goldmaster Writer'),
            groups: [
                new MembershipInputDto($groupId, ['coder', 'musician'], $this->date('1991-02-03'), $this->date('1995-06-07')),
                new MembershipInputDto($groupAliasId),
            ],
            prods: [new MembershipInputDto($prodId, ['music'])],
            importOrigins: [new ImportOriginDto(ImportOrigin::Zxdb, '9001'), new ImportOriginDto(ImportOrigin::Pouet, '77')],
        ))->id;

        self::assertSame(
            [['roles' => ['coder', 'musician'], 'startDate' => $this->date('1991-02-03'), 'endDate' => $this->date('1995-06-07')]],
            $this->authorshipRecords($groupId, $authorId, 'group'),
        );
        self::assertSame([['roles' => ['unknown'], 'startDate' => 0, 'endDate' => 0]], $this->authorshipRecords($groupAliasId, $authorId, 'group'));
        self::assertSame([['roles' => ['music'], 'startDate' => 0, 'endDate' => 0]], $this->authorshipRecords($prodId, $authorId, 'prod'));
        self::assertSame(
            [['origin' => 'pouet', 'importId' => '77'], ['origin' => 'zxdb', 'importId' => '9001']],
            $this->storedImportOrigins($authorId),
        );
    }

    public function testUpdateReplacesTheListsGivenAndKeepsTheOnesLeftOut(): void
    {
        $this->signInAsEditor();
        $groupId = $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand(new GroupFieldsDto(title: 'Goldmaster Guild')))->id;
        $otherGroupId = $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand(new GroupFieldsDto(title: 'Goldmaster Union')))->id;
        $prodId = $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: 'Goldmaster Demo')))->id;
        $authorId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(
            new AuthorFieldsDto(title: 'Goldmaster Writer'),
            groups: [new MembershipInputDto($groupId, ['coder'])],
            prods: [new MembershipInputDto($prodId, ['music'])],
            importOrigins: [new ImportOriginDto(ImportOrigin::Zxdb, '9001')],
        ))->id;

        $this->container->get(UpdateAuthorHandler::class)->handle(new UpdateAuthorCommand(
            $authorId,
            new AuthorFieldsDto(),
            groups: [new MembershipInputDto($otherGroupId, ['graphician'])],
            importOrigins: [],
        ));

        self::assertSame([], $this->authorshipRecords($groupId, $authorId, 'group'));
        self::assertSame([['roles' => ['graphician'], 'startDate' => 0, 'endDate' => 0]], $this->authorshipRecords($otherGroupId, $authorId, 'group'));
        self::assertSame([['roles' => ['music'], 'startDate' => 0, 'endDate' => 0]], $this->authorshipRecords($prodId, $authorId, 'prod'));
        self::assertSame([], $this->storedImportOrigins($authorId));
    }

    public function testAWrongMembershipRefusesTheWholeUpdate(): void
    {
        $this->signInAsEditor();
        $groupId = $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand(new GroupFieldsDto(title: 'Goldmaster Guild')))->id;
        $prodId = $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: 'Goldmaster Demo')))->id;
        $authorId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(
            new AuthorFieldsDto(title: 'Goldmaster Writer'),
            groups: [new MembershipInputDto($groupId, ['coder'])],
        ))->id;

        $this->assertRefused(400, AuthorDataException::class, fn() => $this->container->get(UpdateAuthorHandler::class)->handle(new UpdateAuthorCommand(
            $authorId,
            new AuthorFieldsDto(title: 'Zeta Goldmaster'),
            groups: [new MembershipInputDto($prodId)],
            importOrigins: [new ImportOriginDto(ImportOrigin::Zxdb, '9001')],
        )));

        self::assertSame(['title' => 'Goldmaster Writer'], $this->storedRow(self::TABLE, $authorId, ['title']));
        self::assertSame([['roles' => ['coder'], 'startDate' => 0, 'endDate' => 0]], $this->authorshipRecords($groupId, $authorId, 'group'));
        self::assertSame([], $this->storedImportOrigins($authorId));
    }

    public function testAWrongRoleRefusesTheCreateAndCreatesNothing(): void
    {
        $this->signInAsRegisteredUser();
        $groupId = $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand(new GroupFieldsDto(title: 'Goldmaster Guild')))->id;
        $createdBefore = $this->newElementIds();

        $this->assertRefused(400, AuthorDataException::class, fn() => $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(
            new AuthorFieldsDto(title: 'Goldmaster Writer'),
            groups: [new MembershipInputDto($groupId, ['juggler'])],
        )));

        self::assertSame($createdBefore, $this->newElementIds());
    }

    public function testJoinMovesTheWorksAndAliasesAndDeletesTheJoinedAuthor(): void
    {
        $this->signInAsJoiner();
        $mainId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: 'Goldmaster Writer')))->id;
        $joinedId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(
            title: 'Goldmaster Painter',
            countryId: self::COUNTRY_UNITED_KINGDOM,
            cityId: self::CITY_LONDON,
        )))->id;
        $aliasId = $this->container->get(CreateAuthorAliasHandler::class)->handle(new CreateAuthorAliasCommand(new AuthorAliasFieldsDto(title: 'Painter Alias', authorId: $joinedId)))->id;
        $prodId = $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: 'Goldmaster Demo')))->id;
        $this->addAuthorship($joinedId, $prodId, 'prod');

        $this->container->get(JoinAuthorsHandler::class)->handle(new JoinAuthorsCommand($mainId, $joinedId));

        self::assertFalse($this->elementExists($joinedId));
        self::assertSame([['authorId' => $mainId, 'elementId' => $prodId, 'type' => 'prod']], $this->authorshipRows($mainId));
        self::assertSame($mainId, $this->storedRow('module_authoralias', $aliasId, ['authorId'])['authorId']);
        self::assertSame(
            ['country' => self::COUNTRY_UNITED_KINGDOM, 'city' => self::CITY_LONDON],
            $this->storedRow(self::TABLE, $mainId, ['country', 'city']),
        );
        self::assertSame(['publicAdd', 'join'], $this->loggedActions($mainId));
    }

    public function testJoinAsAliasKeepsTheJoinedNameAsAnAliasHoldingItsWorks(): void
    {
        $this->signInAsJoiner();
        $mainId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: 'Goldmaster Writer')))->id;
        $joinedId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: 'Goldmaster Painter')))->id;
        $prodId = $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: 'Goldmaster Demo')))->id;
        $this->addAuthorship($joinedId, $prodId, 'prod');
        $createdBefore = $this->newElementIds();

        $this->container->get(JoinAuthorsHandler::class)->handle(new JoinAuthorsCommand($mainId, $joinedId, true));

        self::assertFalse($this->elementExists($joinedId));
        $newIds = array_values(array_diff($this->newElementIds(), $createdBefore));
        self::assertCount(1, $newIds);
        self::assertSame(
            ['title' => 'Goldmaster Painter', 'authorId' => $mainId],
            $this->storedRow('module_authoralias', $newIds[0], ['title', 'authorId']),
        );
        self::assertSame([['authorId' => $newIds[0], 'elementId' => $prodId, 'type' => 'prod']], $this->authorshipRows($newIds[0]));
    }

    public function testJoinIsForbiddenWithoutThePrivilegeAndJoinsNothing(): void
    {
        $this->signInAsRegisteredUser();
        $mainId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: 'Goldmaster Writer')))->id;
        $joinedId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: 'Goldmaster Painter')))->id;

        try {
            $this->container->get(JoinAuthorsHandler::class)->handle(new JoinAuthorsCommand($mainId, $joinedId));
            self::fail('Joining must need author.join');
        } catch (AuthorDataException $exception) {
            self::assertSame(403, $exception->getStatusCode());
        }
        self::assertTrue($this->elementExists($joinedId));
    }

    public function testSearchFindsTheAuthorByRealNameAliasAndGroup(): void
    {
        $this->signInAsRegisteredUser();
        $authorId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: 'Goldmaster Writer', realName: 'Johann Goldmaster')))->id;
        $this->container->get(CreateAuthorAliasHandler::class)->handle(new CreateAuthorAliasCommand(new AuthorAliasFieldsDto(title: 'Goldmaster Ghost', authorId: $authorId)));
        $groupId = $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand(new GroupFieldsDto(title: 'Goldmaster Guild')))->id;
        $this->container->get(SetGroupMemberHandler::class)->handle(new SetGroupMemberCommand($groupId, $authorId, ['coder']));
        $find = $this->finder();

        self::assertContains($authorId, $find(new AuthorSearchFilterDto(name: 'johann goldm')));
        self::assertContains($authorId, $find(new AuthorSearchFilterDto(name: 'GOLDMASTER GHOST')));
        self::assertSame([$authorId], $find(new AuthorSearchFilterDto(groupIds: [$groupId])));
        self::assertNotContains($authorId, $find(new AuthorSearchFilterDto(name: 'Goldmaster_Ghost')));
    }

    public function testSearchFindsTheAuthorsByTheirSoftwareItsYearsTheirRolesAndCoAuthors(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], [['zxProd', 'publicReceive']]);
        $coderId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: 'Goldmaster Coder', cityId: self::CITY_WARSAW)))->id;
        $musicianId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: 'Goldmaster Musician')))->id;
        $tunesId = $this->container->get(CreateAuthorAliasHandler::class)->handle(new CreateAuthorAliasCommand(new AuthorAliasFieldsDto(title: 'Goldmaster Tunes', authorId: $musicianId)))->id;
        $demoId = $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: 'Goldmaster Demo', year: 1990)))->id;
        $gameId = $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: 'Goldmaster Game', year: 1996)))->id;
        $this->container->get(SetProdMemberHandler::class)->handle(new SetProdMemberCommand($demoId, $coderId, ['code']));
        $this->container->get(SetProdMemberHandler::class)->handle(new SetProdMemberCommand($demoId, $tunesId, ['music']));
        $this->container->get(SetProdMemberHandler::class)->handle(new SetProdMemberCommand($gameId, $musicianId, ['music']));
        $find = $this->finder();

        self::assertSame([$coderId, $musicianId], $find(new AuthorSearchFilterDto(name: 'goldmaster', prods: new ProdSearchFilterDto(title: 'goldmaster demo'))));
        self::assertSame([$coderId], $find(new AuthorSearchFilterDto(name: 'goldmaster', prodRoles: ['code'], prods: new ProdSearchFilterDto(title: 'goldmaster'))));
        self::assertSame([$musicianId], $find(new AuthorSearchFilterDto(name: 'goldmaster', prodIds: [$gameId])));
        self::assertSame([$musicianId], $find(new AuthorSearchFilterDto(name: 'goldmaster', activeFrom: 1995)));
        self::assertSame([$coderId, $musicianId], $find(new AuthorSearchFilterDto(name: 'goldmaster', activeTo: 1992)));
        self::assertSame([$musicianId], $find(new AuthorSearchFilterDto(coAuthorId: $coderId)));
        self::assertSame([$coderId], $find(new AuthorSearchFilterDto(name: 'goldmaster', cityIds: [self::CITY_WARSAW])));
    }

    public function testAnAuthorWhoPublishedSoftwareIsLinkedToIt(): void
    {
        $this->signInAsRegisteredUser();
        $publisherId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: 'Goldmaster Publisher')))->id;
        $gameId = $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: 'Goldmaster Game', year: 1996, publisherIds: [$publisherId])))->id;
        $find = $this->finder();

        self::assertSame([$publisherId], $find(new AuthorSearchFilterDto(name: 'goldmaster', prodIds: [$gameId], prodLink: ProdLink::Published)));
        self::assertSame([], $find(new AuthorSearchFilterDto(name: 'goldmaster', prodIds: [$gameId], prodLink: ProdLink::Made)));
        self::assertSame([$publisherId], $find(new AuthorSearchFilterDto(name: 'goldmaster', activeFrom: 1996)));
        self::assertSame([$gameId], $this->container->get(ProdSearchService::class)
            ->search(new ProdSearchFilterDto(title: 'goldmaster', publisherIds: [$publisherId]), 0, 10)->ids);
    }

    /**
     * @return Closure(AuthorSearchFilterDto): list<int>
     */
    private function finder(): Closure
    {
        $search = $this->container->get(AuthorSearchService::class);

        return static fn(AuthorSearchFilterDto $filter): array => $search->search($filter, 0, 50)->ids;
    }

    private function signInAsRegisteredUser(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS]);
    }

    /**
     * A registered user who may also edit every author.
     */
    private function signInAsEditor(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], [['author', 'publicReceive']]);
    }

    /**
     * A registered user who may also join any authors.
     */
    private function signInAsJoiner(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], [['author', 'join']]);
    }

    private function everyField(): AuthorFieldsDto
    {
        return new AuthorFieldsDto(
            title: 'Goldmaster Writer',
            realName: 'Paint Master Writer',
            countryId: self::COUNTRY_POLAND,
            cityId: self::CITY_WARSAW,
            wikiLink: 'https://wiki.invalid/goldmaster',
            denyVoting: true,
            denyComments: true,
            deny3a: true,
            displayInMusic: true,
            displayInGraphics: false,
            chipType: 'ym',
            channelsType: 'BAC',
            frequency: '1773400',
            intFrequency: '50',
            palette: 'pulsar',
            zxTunesId: 99001,
        );
    }

    /**
     * {@see self::everyField()} as the table holds it, in every language.
     *
     * @return array<string, mixed>
     */
    private function everyFieldStored(): array
    {
        return [
            'title' => 'Goldmaster Writer',
            'realName' => 'Paint Master Writer',
            'country' => self::COUNTRY_POLAND,
            'city' => self::CITY_WARSAW,
            'wikiLink' => 'https://wiki.invalid/goldmaster',
            'denyVoting' => 1,
            'denyComments' => 1,
            'deny3a' => 1,
            'displayInMusic' => 1,
            'displayInGraphics' => 0,
            'chipType' => 'ym',
            'channelsType' => 'BAC',
            'frequency' => 1773400,
            'intFrequency' => '50',
            'palette' => 'pulsar',
            'zxTunesId' => 99001,
        ];
    }

    /**
     * A real name of its own for each language.
     *
     * @return array<int, string>
     */
    private function realNamesPerLanguage(): array
    {
        $realNames = [];
        foreach ($this->languageIds() as $number => $languageId) {
            $realNames[$languageId] = 'Paint Master Writer ' . ($number + 1);
        }

        return $realNames;
    }

    /**
     * @return array<string, mixed>
     */
    private function everyFieldStoredButRealName(): array
    {
        $stored = $this->everyFieldStored();
        unset($stored['realName']);

        return $stored;
    }

    /**
     * @return array<int, string>
     */
    private function forEveryLanguage(string $value): array
    {
        return array_fill_keys($this->languageIds(), $value);
    }

    /**
     * @return list<int>
     */
    private function languageIds(): array
    {
        $ids = array_map('intval', $this->container->get(LanguagesManager::class)->getLanguagesIdList());
        sort($ids);

        return $ids;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function authorRows(): array
    {
        $newIds = $this->newElementIds();

        return $newIds === [] ? [] : $this->db->table(self::TABLE)->whereIn('id', $newIds)->get();
    }

    private function element(int $elementId): authorElement
    {
        $element = $this->structureManager->getElementById($elementId);
        self::assertInstanceOf(authorElement::class, $element);

        return $element;
    }
}
