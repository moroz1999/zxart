<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Entities;

require_once __DIR__ . '/EntityWriteTestCase.php';

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ZxArt\Authors\Dto\AuthorAliasFieldsDto;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\Exception\AuthorAliasDataException;
use ZxArt\Authors\Services\AuthorAliasDataService;
use ZxArt\Authors\Services\AuthorDataService;
use ZxArt\Authors\Dto\MembershipInputDto;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Groups\Services\GroupDataService;
use ZxArt\Import\Dto\ImportOriginDto;
use ZxArt\Import\ImportOrigin;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\Services\ProdDataService;
use ZxArt\Authors\Services\AuthorAliasFieldsReader;
use authorAliasElement;

/**
 * Author aliases written through {@see AuthorAliasDataService}.
 */
#[RunTestsInSeparateProcesses]
final class AuthorAliasWritesTest extends EntityWriteTestCase
{
    private const string TABLE = 'module_authoralias';
    private const int COUNTRY_POLAND = 47450;
    private const array COLUMNS = ['title', 'authorId', 'startDate', 'endDate', 'displayInMusic', 'displayInGraphics'];

    public function testCreateStoresEveryFieldUnderTheLetterOfItsName(): void
    {
        $this->signInAsRegisteredUser();
        $authorId = $this->createAuthor('Goldmaster Writer');

        $aliasId = $this->aliasDataService()->create($this->everyField($authorId))->id;

        self::assertSame($this->everyFieldStored($authorId), $this->storedRow(self::TABLE, $aliasId, self::COLUMNS));
        self::assertSame(['structureType' => 'letter', 'structureName' => 'w'], $this->letterOf($aliasId));
        self::assertSame('wandering-goldmaster', $this->structureName($aliasId));
    }

    public function testCreateLetsTheCreatorKeepEditingAndIsLogged(): void
    {
        $this->signInAsRegisteredUser();
        $authorId = $this->createAuthor('Goldmaster Writer');

        $aliasId = $this->aliasDataService()->create(new AuthorAliasFieldsDto(title: 'Wandering Goldmaster', authorId: $authorId))->id;

        self::assertSame(
            ['authorAlias.publicDelete', 'authorAlias.publicReceive', 'authorAlias.showPublicForm'],
            $this->allowedOnElement($aliasId),
        );
        self::assertSame(['publicAdd'], $this->loggedActions($aliasId));
    }

    public function testTheCreatorMayUpdateTheAliasRightAway(): void
    {
        $this->signInAsRegisteredUser();
        $authorId = $this->createAuthor('Goldmaster Writer');
        $aliasId = $this->aliasDataService()->create(new AuthorAliasFieldsDto(title: 'Wandering Goldmaster', authorId: $authorId))->id;

        $this->aliasDataService()->update($aliasId, new AuthorAliasFieldsDto(displayInMusic: true));

        self::assertSame(1, $this->storedRow(self::TABLE, $aliasId, ['displayInMusic'])['displayInMusic']);
    }

    public function testCreateForAnAuthorNothingHasLoadedYet(): void
    {
        $this->signInAsRegisteredUser();
        $authorId = (int)$this->db->table('module_author')->min('id');

        $aliasId = $this->aliasDataService()->create(new AuthorAliasFieldsDto(title: 'Wandering Goldmaster', authorId: $authorId))->id;

        self::assertSame($authorId, $this->storedRow(self::TABLE, $aliasId, ['authorId'])['authorId']);
    }

    public function testCreateWithoutTitleOrAuthorIsRejected(): void
    {
        $this->signInAsRegisteredUser();
        $authorId = $this->createAuthor('Goldmaster Writer');
        $createdBefore = $this->newElementIds();

        foreach ([
            new AuthorAliasFieldsDto(title: '', authorId: $authorId),
            new AuthorAliasFieldsDto(title: 'Wandering Goldmaster'),
        ] as $fields) {
            try {
                $this->aliasDataService()->create($fields);
                self::fail('An alias needs a name and its author');
            } catch (AuthorAliasDataException $exception) {
                self::assertSame(400, $exception->getStatusCode());
            }
        }
        self::assertSame($createdBefore, $this->newElementIds());
    }

    public function testCreateForWhatIsNotAnAuthorWritesNothing(): void
    {
        $this->signInAsRegisteredUser();

        try {
            $this->aliasDataService()->create(new AuthorAliasFieldsDto(title: 'Wandering Goldmaster', authorId: self::COUNTRY_POLAND));
            self::fail('A country given as the author must be rejected');
        } catch (AuthorAliasDataException $exception) {
            self::assertSame(400, $exception->getStatusCode());
        }
        self::assertSame([], $this->newElementIds());
    }

    public function testCreateIsForbiddenWithoutThePrivilege(): void
    {
        $this->signIn([self::PUBLIC_VISITORS]);
        $authorId = (int)$this->db->table('module_author')->min('id');
        $this->structureManager->getElementById($authorId);

        try {
            $this->aliasDataService()->create(new AuthorAliasFieldsDto(title: 'Wandering Goldmaster', authorId: $authorId));
            self::fail('Creating must need authorAlias.publicAdd');
        } catch (AuthorAliasDataException $exception) {
            self::assertSame(403, $exception->getStatusCode());
        }
        self::assertSame([], $this->newElementIds());
    }

    public function testUpdateChangesTheGivenFieldsAndKeepsTheOthers(): void
    {
        $this->signInAsEditor();
        $authorId = $this->createAuthor('Goldmaster Writer');
        $aliasId = $this->aliasDataService()->create($this->everyField($authorId))->id;

        $this->aliasDataService()->update($aliasId, new AuthorAliasFieldsDto(
            endDate: $this->date('31.12.2001'),
            displayInMusic: false,
        ));

        self::assertSame(
            array_replace($this->everyFieldStored($authorId), ['endDate' => $this->date('31.12.2001'), 'displayInMusic' => 0]),
            $this->storedRow(self::TABLE, $aliasId, self::COLUMNS),
        );
        self::assertSame(['publicAdd', 'publicReceive'], $this->loggedActions($aliasId));
    }

    public function testUpdateClearsTheDatesWithZero(): void
    {
        $this->signInAsEditor();
        $authorId = $this->createAuthor('Goldmaster Writer');
        $aliasId = $this->aliasDataService()->create($this->everyField($authorId))->id;

        $this->aliasDataService()->update($aliasId, new AuthorAliasFieldsDto(startDate: 0, endDate: 0));

        self::assertSame(
            array_replace($this->everyFieldStored($authorId), ['startDate' => 0, 'endDate' => 0]),
            $this->storedRow(self::TABLE, $aliasId, self::COLUMNS),
        );
    }

    public function testRenameMovesTheAliasToTheLetterOfTheNewNameAndAnotherAuthorTakesIt(): void
    {
        $this->signInAsEditor();
        $authorId = $this->createAuthor('Goldmaster Writer');
        $otherAuthorId = $this->createAuthor('Goldmaster Painter');
        $aliasId = $this->aliasDataService()->create(new AuthorAliasFieldsDto(title: 'Wandering Goldmaster', authorId: $authorId))->id;

        $this->aliasDataService()->update($aliasId, new AuthorAliasFieldsDto(title: 'Zeta Goldmaster', authorId: $otherAuthorId));

        self::assertSame(['title' => 'Zeta Goldmaster', 'authorId' => $otherAuthorId], $this->storedRow(self::TABLE, $aliasId, ['title', 'authorId']));
        self::assertSame(['structureType' => 'letter', 'structureName' => 'z'], $this->letterOf($aliasId));
        self::assertSame('zeta-goldmaster', $this->structureName($aliasId));
    }

    public function testUpdateToWhatIsNotAnAuthorChangesNothing(): void
    {
        $this->signInAsEditor();
        $authorId = $this->createAuthor('Goldmaster Writer');
        $aliasId = $this->aliasDataService()->create($this->everyField($authorId))->id;

        try {
            $this->aliasDataService()->update($aliasId, new AuthorAliasFieldsDto(title: 'Zeta Goldmaster', authorId: self::COUNTRY_POLAND));
            self::fail('A country given as the author must be rejected');
        } catch (AuthorAliasDataException $exception) {
            self::assertSame(400, $exception->getStatusCode());
        }
        self::assertSame($this->everyFieldStored($authorId), $this->storedRow(self::TABLE, $aliasId, self::COLUMNS));
    }

    public function testUpdateIsForbiddenWithoutThePrivilegeOnTheAlias(): void
    {
        $this->signInAsRegisteredUser();
        $aliasId = (int)$this->db->table(self::TABLE)->min('id');
        $stored = $this->db->table(self::TABLE)->where('id', '=', $aliasId)->get();

        try {
            $this->aliasDataService()->update($aliasId, new AuthorAliasFieldsDto(title: 'Zeta Goldmaster'));
            self::fail('Updating must need authorAlias.publicReceive');
        } catch (AuthorAliasDataException $exception) {
            self::assertSame(403, $exception->getStatusCode());
        }
        self::assertSame($stored, $this->db->table(self::TABLE)->where('id', '=', $aliasId)->get());
    }

    public function testCreateStoresTheGroupsProductionsAndPortalIds(): void
    {
        $this->signInAsRegisteredUser();
        $authorId = $this->createAuthor('Goldmaster Writer');
        $groupId = $this->container->get(GroupDataService::class)->create(new GroupFieldsDto(title: 'Goldmaster Guild'))->id;
        $prodId = $this->container->get(ProdDataService::class)->create(new ProdFieldsDto(title: 'Goldmaster Demo'))->id;

        $aliasId = $this->aliasDataService()->create(
            new AuthorAliasFieldsDto(title: 'Wandering Goldmaster', authorId: $authorId),
            groups: [new MembershipInputDto($groupId, ['musician'], $this->date('1993-01-01'))],
            prods: [new MembershipInputDto($prodId, ['music', 'sfx'])],
            importOrigins: [new ImportOriginDto(ImportOrigin::Zxdb, '9002')],
        )->id;

        self::assertSame(
            [['roles' => ['musician'], 'startDate' => $this->date('1993-01-01'), 'endDate' => 0]],
            $this->authorshipRecords($groupId, $aliasId, 'group'),
        );
        self::assertSame([['roles' => ['music', 'sfx'], 'startDate' => 0, 'endDate' => 0]], $this->authorshipRecords($prodId, $aliasId, 'prod'));
        self::assertSame([['origin' => 'zxdb', 'importId' => '9002']], $this->storedImportOrigins($aliasId));
    }

    public function testUpdateReplacesTheListsGivenAndKeepsTheOnesLeftOut(): void
    {
        $this->signInAsEditor();
        $authorId = $this->createAuthor('Goldmaster Writer');
        $groupId = $this->container->get(GroupDataService::class)->create(new GroupFieldsDto(title: 'Goldmaster Guild'))->id;
        $prodId = $this->container->get(ProdDataService::class)->create(new ProdFieldsDto(title: 'Goldmaster Demo'))->id;
        $aliasId = $this->aliasDataService()->create(
            new AuthorAliasFieldsDto(title: 'Wandering Goldmaster', authorId: $authorId),
            groups: [new MembershipInputDto($groupId, ['musician'])],
            importOrigins: [new ImportOriginDto(ImportOrigin::Zxdb, '9002')],
        )->id;

        $this->aliasDataService()->update(
            $aliasId,
            new AuthorAliasFieldsDto(title: 'Zeta Goldmaster'),
            groups: [],
            prods: [new MembershipInputDto($prodId, ['code'])],
        );

        self::assertSame(['title' => 'Zeta Goldmaster'], $this->storedRow(self::TABLE, $aliasId, ['title']));
        self::assertSame([], $this->authorshipRecords($groupId, $aliasId, 'group'));
        self::assertSame([['roles' => ['code'], 'startDate' => 0, 'endDate' => 0]], $this->authorshipRecords($prodId, $aliasId, 'prod'));
        self::assertSame([['origin' => 'zxdb', 'importId' => '9002']], $this->storedImportOrigins($aliasId));
    }

    public function testAWrongMembershipRefusesTheWholeUpdate(): void
    {
        $this->signInAsEditor();
        $authorId = $this->createAuthor('Goldmaster Writer');
        $aliasId = $this->aliasDataService()->create(new AuthorAliasFieldsDto(title: 'Wandering Goldmaster', authorId: $authorId))->id;

        $this->assertRefused(400, AuthorAliasDataException::class, fn() => $this->aliasDataService()->update(
            $aliasId,
            new AuthorAliasFieldsDto(title: 'Zeta Goldmaster'),
            prods: [new MembershipInputDto($authorId, ['code'])],
            importOrigins: [new ImportOriginDto(ImportOrigin::Zxdb, '9002')],
        ));

        self::assertSame(['title' => 'Wandering Goldmaster'], $this->storedRow(self::TABLE, $aliasId, ['title']));
        self::assertSame([], $this->storedImportOrigins($aliasId));
    }

    public function testAWrongRoleRefusesTheCreateAndCreatesNothing(): void
    {
        $this->signInAsRegisteredUser();
        $authorId = $this->createAuthor('Goldmaster Writer');
        $prodId = $this->container->get(ProdDataService::class)->create(new ProdFieldsDto(title: 'Goldmaster Demo'))->id;
        $createdBefore = $this->newElementIds();

        $this->assertRefused(400, AuthorAliasDataException::class, fn() => $this->aliasDataService()->create(
            new AuthorAliasFieldsDto(title: 'Wandering Goldmaster', authorId: $authorId),
            prods: [new MembershipInputDto($prodId, ['coder'])],
        ));

        self::assertSame($createdBefore, $this->newElementIds());
    }

    public function testJoinMovesTheWorksOfTheJoinedAliasAndDeletesIt(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], [['authorAlias', 'join']]);
        $authorId = $this->container->get(AuthorDataService::class)->create(new AuthorFieldsDto(title: 'Goldmaster Writer'))->id;
        $mainId = $this->aliasDataService()->create(new AuthorAliasFieldsDto(title: 'Wandering Writer', authorId: $authorId))->id;
        $joinedId = $this->aliasDataService()->create(new AuthorAliasFieldsDto(title: 'Wandering Painter', authorId: $authorId))->id;
        $prodId = $this->container->get(ProdDataService::class)->create(new ProdFieldsDto(title: 'Goldmaster Demo'))->id;
        $this->addAuthorship($joinedId, $prodId, 'prod');

        $this->aliasDataService()->join($mainId, $joinedId);

        self::assertFalse($this->elementExists($joinedId));
        self::assertSame([['authorId' => $mainId, 'elementId' => $prodId, 'type' => 'prod']], $this->authorshipRows($mainId));
        self::assertSame(['publicAdd', 'join'], $this->loggedActions($mainId));
    }

    public function testJoinIsForbiddenWithoutThePrivilegeAndJoinsNothing(): void
    {
        $this->signInAsRegisteredUser();
        $authorId = $this->container->get(AuthorDataService::class)->create(new AuthorFieldsDto(title: 'Goldmaster Writer'))->id;
        $mainId = $this->aliasDataService()->create(new AuthorAliasFieldsDto(title: 'Wandering Writer', authorId: $authorId))->id;
        $joinedId = $this->aliasDataService()->create(new AuthorAliasFieldsDto(title: 'Wandering Painter', authorId: $authorId))->id;

        try {
            $this->aliasDataService()->join($mainId, $joinedId);
            self::fail('Joining must need authorAlias.join');
        } catch (AuthorAliasDataException $exception) {
            self::assertSame(403, $exception->getStatusCode());
        }
        self::assertTrue($this->elementExists($joinedId));
    }

    public function testReadAnswersWhatCreateAndUpdateWrote(): void
    {
        $this->signInAsEditor();
        $authorId = $this->createAuthor('Goldmaster Writer');
        $otherAuthorId = $this->createAuthor('Goldmaster Painter');
        $aliasId = $this->aliasDataService()->create($this->everyField($authorId))->id;
        $reader = $this->container->get(AuthorAliasFieldsReader::class);

        self::assertEquals($this->everyField($authorId), $reader->read($this->element($aliasId)));

        $this->aliasDataService()->update($aliasId, new AuthorAliasFieldsDto(authorId: $otherAuthorId, endDate: 0));

        self::assertEquals(
            new AuthorAliasFieldsDto(...array_replace(get_object_vars($this->everyField($authorId)), ['authorId' => $otherAuthorId, 'endDate' => 0])),
            $reader->read($this->element($aliasId)),
        );
    }

    private function signInAsRegisteredUser(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS]);
    }

    /**
     * A registered user who may also edit every author alias.
     */
    private function signInAsEditor(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], [['authorAlias', 'publicReceive']]);
    }

    private function createAuthor(string $title): int
    {
        return $this->container->get(AuthorDataService::class)->create(new AuthorFieldsDto(title: $title))->id;
    }

    private function everyField(int $authorId): AuthorAliasFieldsDto
    {
        return new AuthorAliasFieldsDto(
            title: 'Wandering Goldmaster',
            authorId: $authorId,
            startDate: $this->date('15.03.1995'),
            endDate: $this->date('01.06.1999'),
            displayInMusic: true,
            displayInGraphics: false,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function everyFieldStored(int $authorId): array
    {
        return [
            'title' => 'Wandering Goldmaster',
            'authorId' => $authorId,
            'startDate' => $this->date('15.03.1995'),
            'endDate' => $this->date('01.06.1999'),
            'displayInMusic' => 1,
            'displayInGraphics' => 0,
        ];
    }

    private function element(int $elementId): authorAliasElement
    {
        $element = $this->structureManager->getElementById($elementId);
        self::assertInstanceOf(authorAliasElement::class, $element);

        return $element;
    }

    private function aliasDataService(): AuthorAliasDataService
    {
        return $this->container->get(AuthorAliasDataService::class);
    }
}
