<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Tags;

use controller;
use Illuminate\Database\Connection;
use LanguagesManager;
use PHPUnit\Framework\TestCase;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Tags\Dto\TagLookupItemDto;
use ZxArt\Tags\Services\TagLookupService;

/**
 * What a tag picker is offered: the tags of the phrase, the most used first,
 * each named in the current language with its synonyms and description.
 */
final class TagLookupServiceTest extends TestCase
{
    public function testTheMostUsedTagIsOfferedNamedWithItsSynonymsAndDescription(): void
    {
        $tag = $this->mostUsedTag();
        $expectedTitle = html_entity_decode(
            $tag['title']
            . ($tag['synonym'] !== '' ? ', ' . $tag['synonym'] : '')
            . ($tag['description'] !== '' ? ' (' . $tag['description'] . ')' : ''),
            ENT_QUOTES,
        );

        $items = $this->service()->lookup($tag['title']);

        self::assertNotEmpty($items);
        $first = $items[0];
        self::assertInstanceOf(TagLookupItemDto::class, $first);
        self::assertSame($tag['id'], $first->id);
        self::assertSame($expectedTitle, $first->title);
        self::assertSame(html_entity_decode($tag['title'], ENT_QUOTES), $first->value);
        self::assertSame(html_entity_decode($tag['synonym'], ENT_QUOTES), $first->synonym);
        self::assertSame(html_entity_decode($tag['description'], ENT_QUOTES), $first->description);
        self::assertNotSame('', $first->url);
    }

    public function testABlankPhraseOffersNothing(): void
    {
        self::assertSame([], $this->service()->lookup('  '));
    }

    /**
     * @return array{id: int, title: string, synonym: string, description: string}
     */
    private function mostUsedTag(): array
    {
        $container = controller::getInstance()->getContainer();
        /** @var array{id: int, title: string, synonym: string, description: string} $row */
        $row = $container->get(Connection::class)
            ->table(DatabaseTable::Tag->value)
            ->where('languageId', '=', (int)$container->get(LanguagesManager::class)->getCurrentLanguageId())
            ->orderBy('amount', 'desc')
            ->first(['id', 'title', 'synonym', 'description']);
        $row['id'] = (int)$row['id'];

        return $row;
    }

    private function service(): TagLookupService
    {
        return controller::getInstance()->getContainer()->get(TagLookupService::class);
    }
}
