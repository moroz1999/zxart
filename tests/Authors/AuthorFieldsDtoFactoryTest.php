<?php

declare(strict_types=1);

namespace ZxArt\Tests\Authors;

use PHPUnit\Framework\TestCase;
use structureElement;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\Services\AuthorFieldsDtoFactory;

final class AuthorFieldsDtoFactoryTest extends TestCase
{
    public function testReadsEverySubmittedFieldInItsDomainType(): void
    {
        $form = $this->formWith([
            'title' => 'Fikus',
            'realName' => 'Ivan',
            'country' => '3',
            'city' => '4',
            'wikiLink' => 'https://wiki',
            'denyVoting' => '1',
            'denyComments' => '0',
            'deny3a' => '1',
            'displayInMusic' => '1',
            'displayInGraphics' => '0',
            'chipType' => 'ym',
            'channelsType' => 'abc',
            'frequency' => '1773400',
            'intFrequency' => '50',
            'palette' => 'pulsar',
            'zxTunesId' => '12',
        ]);

        $fields = (new AuthorFieldsDtoFactory())->fromSubmittedForm($form, ['title', 'realName', 'country', 'city', 'wikiLink', 'denyVoting', 'denyComments', 'deny3a', 'displayInMusic', 'displayInGraphics', 'chipType', 'channelsType', 'frequency', 'intFrequency', 'palette', 'zxTunesId']);

        self::assertSame('Fikus', $fields->title);
        // the real name is per language and stays on the element
        self::assertNull($fields->realName);
        self::assertSame(3, $fields->countryId);
        self::assertSame(4, $fields->cityId);
        self::assertSame('https://wiki', $fields->wikiLink);
        self::assertTrue($fields->denyVoting);
        self::assertFalse($fields->denyComments);
        self::assertTrue($fields->deny3a);
        self::assertTrue($fields->displayInMusic);
        self::assertFalse($fields->displayInGraphics);
        self::assertSame('ym', $fields->chipType);
        self::assertSame('abc', $fields->channelsType);
        self::assertSame('1773400', $fields->frequency);
        self::assertSame('50', $fields->intFrequency);
        self::assertSame('pulsar', $fields->palette);
        self::assertSame(12, $fields->zxTunesId);
    }

    public function testFieldsTheFormDoesNotSubmitStayUntouched(): void
    {
        $fields = (new AuthorFieldsDtoFactory())->fromSubmittedForm($this->formWith(['title' => 'X']), []);

        self::assertEquals(new AuthorFieldsDto(), $fields);
    }

    /**
     * @param array<string, mixed> $values
     */
    private function formWith(array $values): structureElement
    {
        $form = $this->createStub(structureElement::class);
        $form->method('getValue')->willReturnCallback(
            static fn(string $field): mixed => $values[$field] ?? null,
        );

        return $form;
    }
}
