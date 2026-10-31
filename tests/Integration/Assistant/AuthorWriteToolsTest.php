<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Assistant;

require_once __DIR__ . '/WriteToolTestCase.php';

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ZxArt\Assistant\Tools\Dto\ChangeKind;
use ZxArt\Assistant\Tools\Dto\ChangePreviewDto;
use ZxArt\Assistant\Tools\Dto\ChangeTargetDto;
use ZxArt\Assistant\Tools\Dto\FieldChangeDto;
use ZxArt\Authors\Dto\AuthorAliasDataResultDto;
use ZxArt\Authors\Dto\AuthorAliasFieldsDto;
use ZxArt\Authors\Dto\AuthorDataResultDto;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\Dto\AuthorStateDto;
use ZxArt\Authors\Exception\AuthorDataException;
use ZxArt\Authors\Services\AuthorAliasDataService;
use ZxArt\Authors\Services\AuthorDataService;
use ZxArt\Authors\Services\AuthorStateService;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\Services\ProdDataService;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityMentionDto;

/**
 * The author and author alias write tools.
 */
#[RunTestsInSeparateProcesses]
final class AuthorWriteToolsTest extends WriteToolTestCase
{
    private const int COUNTRY_POLAND = 47450;
    private const int CATEGORY_DEMO = 92159;

    public function testCreateAuthorPreviewsTheGivenFieldsAndCreates(): void
    {
        $this->signInAsEditor();
        $arguments = '{"fields": {"title": "Goldwrite Coder", "countryId": ' . self::COUNTRY_POLAND . '}}';

        self::assertEquals(
            new ChangePreviewDto(
                kind: ChangeKind::Create,
                target: new ChangeTargetDto(EntityType::Author, null, 'Goldwrite Coder'),
                changes: [
                    new FieldChangeDto('title', null, 'Goldwrite Coder'),
                    new FieldChangeDto('countryId', null, new EntityMentionDto(EntityType::Country, self::COUNTRY_POLAND)),
                ],
                consequences: [],
                modifiedAt: null,
            ),
            $this->preview('create_author', $arguments),
        );
        $result = $this->execute('create_author', $arguments);

        self::assertInstanceOf(AuthorDataResultDto::class, $result);
        self::assertSame('Goldwrite Coder', $this->authorState($result->id)->fields->title);
    }

    public function testUpdateAuthorPreviewsWhatChangesAndUpdates(): void
    {
        $this->signInAsEditor();
        $authorId = $this->createAuthor('Goldwrite Coder');
        $arguments = '{"id": ' . $authorId . ', "fields": {"realName": "Jan Kowalski"}}';

        $preview = $this->preview('update_author', $arguments);

        self::assertEquals([new FieldChangeDto('realName', '', 'Jan Kowalski')], $preview->changes);
        self::assertSame($this->modificationTime($authorId), $preview->modifiedAt);
        $this->execute('update_author', $arguments);
        self::assertSame('Jan Kowalski', $this->authorState($authorId)->fields->realName);
    }

    public function testUpdateAuthorIsRefusedAtThePreviewWithoutThePrivilege(): void
    {
        $this->signInAsRegisteredUser();
        $authorId = (int)$this->db->table('module_author')->min('id');

        $this->assertRefused(403, AuthorDataException::class, fn() => $this->preview('update_author', '{"id": ' . $authorId . ', "fields": {"realName": "X"}}'));
    }

    public function testDeleteAuthorPreviewsItsCreditsAndAliases(): void
    {
        $this->signInAsEditor();
        $authorId = $this->createAuthor('Goldwrite Coder');
        $this->creditOnNewProd($authorId);
        $this->createAlias($authorId, 'Goldie');

        $preview = $this->preview('delete_author', '{"id": ' . $authorId . '}');

        self::assertSame(ChangeKind::Delete, $preview->kind);
        self::assertSame(["[[author:$authorId]] is credited on 1 production", 'It has 1 alias'], $preview->consequences);
        $this->execute('delete_author', '{"id": ' . $authorId . '}');
        self::assertFalse($this->elementExists($authorId));
    }

    public function testJoinAuthorsPreviewsWhatMovesAndJoins(): void
    {
        $this->signInAsEditor();
        $mainId = $this->createAuthor('Goldwrite Coder');
        $joinedId = $this->createAuthor('Goldwrite Koder');
        $this->creditOnNewProd($joinedId);
        $arguments = '{"mainId": ' . $mainId . ', "joinedId": ' . $joinedId . '}';

        $preview = $this->preview('join_authors', $arguments);

        self::assertSame(ChangeKind::Join, $preview->kind);
        self::assertEquals(new ChangeTargetDto(EntityType::Author, $mainId, 'Goldwrite Coder'), $preview->target);
        self::assertSame(
            [
                "[[author:$joinedId]] is deleted",
                "Its credits on 1 production move to [[author:$mainId]]",
                "Its aliases, group memberships and import origins move to [[author:$mainId]]",
            ],
            $preview->consequences,
        );
        $this->execute('join_authors', $arguments);
        self::assertFalse($this->elementExists($joinedId));
    }

    public function testJoinAuthorsAsAliasPreviewsTheNewAlias(): void
    {
        $this->signInAsEditor();
        $mainId = $this->createAuthor('Goldwrite Coder');
        $joinedId = $this->createAuthor('Goldwrite Koder');

        self::assertSame(
            [
                "[[author:$joinedId]] is deleted",
                "A new alias of [[author:$mainId]] takes its title and its credits",
                "Its aliases, group memberships and import origins move to [[author:$mainId]]",
            ],
            $this->preview('join_authors', '{"mainId": ' . $mainId . ', "joinedId": ' . $joinedId . ', "asAlias": true}')->consequences,
        );
    }

    public function testConvertAuthorToGroupPreviewsTheReplacementAndConverts(): void
    {
        $this->signInAsEditor();
        $authorId = $this->createAuthor('Goldwrite Crew');

        $preview = $this->preview('convert_author_to_group', '{"id": ' . $authorId . '}');

        self::assertSame(ChangeKind::Convert, $preview->kind);
        self::assertSame(
            ["[[author:$authorId]] is deleted; a new group of the same title takes its productions as made by the group, its releases as published by it, its publications and press mentions"],
            $preview->consequences,
        );
        $result = $this->execute('convert_author_to_group', '{"id": ' . $authorId . '}');
        self::assertInstanceOf(AuthorDataResultDto::class, $result);
        self::assertSame('group', $this->db->table('structure_elements')->where('id', '=', $result->id)->value('structureType'));
    }

    public function testCreateAuthorAliasPreviewsItsAuthorAndDatesAndCreates(): void
    {
        $this->signInAsEditor();
        $authorId = $this->createAuthor('Goldwrite Coder');
        $start = (int)strtotime('1994-05-01 12:00');
        $arguments = '{"fields": {"title": "Goldie", "authorId": ' . $authorId . ', "startDate": ' . $start . '}}';

        self::assertEquals(
            [
                new FieldChangeDto('title', null, 'Goldie'),
                new FieldChangeDto('authorId', null, new EntityMentionDto(EntityType::Author, $authorId)),
                new FieldChangeDto('startDate', null, '1994-05-01'),
            ],
            $this->preview('create_author_alias', $arguments)->changes,
        );
        $result = $this->execute('create_author_alias', $arguments);

        self::assertInstanceOf(AuthorAliasDataResultDto::class, $result);
        self::assertSame([$result->id], $this->authorState($authorId)->aliasIds);
    }

    public function testUpdateAndDeleteAuthorAliasPreviewAndWrite(): void
    {
        $this->signInAsEditor();
        $aliasId = $this->createAlias($this->createAuthor('Goldwrite Coder'), 'Goldie');

        self::assertEquals(
            [new FieldChangeDto('title', 'Goldie', 'Goldy')],
            $this->preview('update_author_alias', '{"id": ' . $aliasId . ', "fields": {"title": "Goldy"}}')->changes,
        );
        $this->execute('update_author_alias', '{"id": ' . $aliasId . ', "fields": {"title": "Goldy"}}');

        $preview = $this->preview('delete_author_alias', '{"id": ' . $aliasId . '}');
        self::assertEquals(new ChangeTargetDto(EntityType::AuthorAlias, $aliasId, 'Goldy'), $preview->target);
        $this->execute('delete_author_alias', '{"id": ' . $aliasId . '}');
        self::assertFalse($this->elementExists($aliasId));
    }

    public function testJoinAuthorAliasesPreviewsWhatMoves(): void
    {
        $this->signInAsEditor();
        $authorId = $this->createAuthor('Goldwrite Coder');
        $mainId = $this->createAlias($authorId, 'Goldie');
        $joinedId = $this->createAlias($authorId, 'Goldy');
        $this->creditOnNewProd($joinedId);
        $arguments = '{"mainId": ' . $mainId . ', "joinedId": ' . $joinedId . '}';

        self::assertSame(
            ["[[authorAlias:$joinedId]] is deleted", "Its credits on 1 production move to [[authorAlias:$mainId]]"],
            $this->preview('join_author_aliases', $arguments)->consequences,
        );
        $this->execute('join_author_aliases', $arguments);
        self::assertFalse($this->elementExists($joinedId));
    }

    public function testConvertAuthorAliasToAuthorPreviewsTheReplacementAndConverts(): void
    {
        $this->signInAsEditor();
        $aliasId = $this->createAlias($this->createAuthor('Goldwrite Coder'), 'Goldie');

        self::assertSame(
            ["[[authorAlias:$aliasId]] is deleted; a new author of the same title takes its credits, links and import origins"],
            $this->preview('convert_author_alias_to_author', '{"id": ' . $aliasId . '}')->consequences,
        );
        $result = $this->execute('convert_author_alias_to_author', '{"id": ' . $aliasId . '}');
        self::assertInstanceOf(AuthorAliasDataResultDto::class, $result);
        self::assertSame('author', $this->db->table('structure_elements')->where('id', '=', $result->id)->value('structureType'));
    }

    private function createAuthor(string $title): int
    {
        return $this->container->get(AuthorDataService::class)->create(new AuthorFieldsDto(title: $title))->id;
    }

    private function createAlias(int $authorId, string $title): int
    {
        return $this->container->get(AuthorAliasDataService::class)->create(new AuthorAliasFieldsDto(title: $title, authorId: $authorId))->id;
    }

    private function creditOnNewProd(int $authorId): void
    {
        // the counters count the productions of a category
        $prodId = $this->container->get(ProdDataService::class)
            ->create(new ProdFieldsDto(title: 'Goldwrite Demo', categoryIds: [self::CATEGORY_DEMO]))->id;
        $this->addAuthorship($authorId, $prodId, 'prod');
    }

    private function authorState(int $authorId): AuthorStateDto
    {
        return $this->container->get(AuthorStateService::class)->get($authorId);
    }
}
