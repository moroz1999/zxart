<?php

declare(strict_types=1);

namespace ZxArt\Tests\Shared\References;

require_once __DIR__ . '/../../Doubles/Elements/ZxProdElementSpy.php';
require_once __DIR__ . '/../../Doubles/Elements/ZxReleaseElementSpy.php';
require_once __DIR__ . '/../../Doubles/Elements/AuthorElementSpy.php';
require_once __DIR__ . '/../../Doubles/Elements/AuthorAliasElementSpy.php';
require_once __DIR__ . '/../../Doubles/Elements/GroupElementSpy.php';
require_once __DIR__ . '/../../Doubles/Elements/GroupAliasElementSpy.php';

use controller;
use PHPUnit\Framework\TestCase;
use structureElement;
use structureManager;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityMentionDto;
use ZxArt\Shared\References\EntityMentionLookup;
use ZxArt\Shared\References\MentionedEntityDto;
use ZxArt\Tests\Doubles\Elements\AuthorAliasElementSpy;
use ZxArt\Tests\Doubles\Elements\AuthorElementSpy;
use ZxArt\Tests\Doubles\Elements\GroupAliasElementSpy;
use ZxArt\Tests\Doubles\Elements\GroupElementSpy;
use ZxArt\Tests\Doubles\Elements\ZxProdElementSpy;
use ZxArt\Tests\Doubles\Elements\ZxReleaseElementSpy;
use ZxArt\Urls\EntityUrlResolver;

class EntityMentionLookupTest extends TestCase
{
    public function testAnswersTheDecodedTitleAndThePageOfEachType(): void
    {
        $lookup = $this->createLookup([
            1 => $this->titled(new ZxProdElementSpy(1), 'Rock &amp; Roll'),
            2 => $this->titled(new ZxReleaseElementSpy(2), 'Tape'),
            3 => $this->titled(new AuthorElementSpy(3), 'Writer'),
            4 => $this->titled(new AuthorAliasElementSpy(4), 'Pen'),
            5 => $this->titled(new GroupElementSpy(5), 'Guild'),
            6 => $this->titled(new GroupAliasElementSpy(6), 'Union'),
        ]);

        self::assertEquals([
            'prod:1' => new MentionedEntityDto('Rock & Roll', '/prod/1'),
            'release:2' => new MentionedEntityDto('Tape', '/release/2'),
            'author:3' => new MentionedEntityDto('Writer', '/author/3'),
            'authorAlias:4' => new MentionedEntityDto('Pen', '/author/4'),
            'group:5' => new MentionedEntityDto('Guild', '/group/5'),
            'groupAlias:6' => new MentionedEntityDto('Union', '/group/6'),
        ], $lookup->lookup([
            new EntityMentionDto(EntityType::Prod, 1),
            new EntityMentionDto(EntityType::Release, 2),
            new EntityMentionDto(EntityType::Author, 3),
            new EntityMentionDto(EntityType::AuthorAlias, 4),
            new EntityMentionDto(EntityType::Group, 5),
            new EntityMentionDto(EntityType::GroupAlias, 6),
        ]));
    }

    public function testAMissingElementOrOneOfAnotherTypeIsLeftOut(): void
    {
        $lookup = $this->createLookup([3 => $this->titled(new AuthorElementSpy(3), 'Writer')]);

        self::assertSame([], $lookup->lookup([
            new EntityMentionDto(EntityType::Prod, 99),
            new EntityMentionDto(EntityType::Group, 3),
            new EntityMentionDto(EntityType::Picture, 3),
        ]));
    }

    public function testARefAskedTwiceIsAnsweredOnce(): void
    {
        $lookup = $this->createLookup([1 => $this->titled(new ZxProdElementSpy(1), 'Demo')]);

        self::assertEquals(
            ['prod:1' => new MentionedEntityDto('Demo', '/prod/1')],
            $lookup->lookup([new EntityMentionDto(EntityType::Prod, 1), new EntityMentionDto(EntityType::Prod, 1)]),
        );
    }

    public function testTheKeyIsTheTypeAndTheId(): void
    {
        self::assertSame('authorAlias:8', (new EntityMentionDto(EntityType::AuthorAlias, 8))->key());
    }

    private function titled(structureElement $element, string $title): structureElement
    {
        $element->title = $title;

        return $element;
    }

    /**
     * @param array<int, structureElement> $elements
     */
    private function createLookup(array $elements): EntityMentionLookup
    {
        $structureManager = $this->createStub(structureManager::class);
        $structureManager->method('getElementById')->willReturnCallback(
            static fn(int $id): ?structureElement => $elements[$id] ?? null,
        );

        return new EntityMentionLookup($structureManager, new EntityUrlResolver($this->createStub(controller::class)));
    }
}
