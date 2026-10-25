<?php

declare(strict_types=1);

namespace ZxArt\Tests\Groups;

use PHPUnit\Framework\TestCase;
use structureElement;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Groups\Services\GroupFieldsDtoFactory;

final class GroupFieldsDtoFactoryTest extends TestCase
{
    public function testReadsEverySubmittedFieldInItsDomainType(): void
    {
        $form = $this->formWith([
            'title' => 'Fenomen',
            'abbreviation' => 'FNM',
            'type' => 'group',
            'country' => '3',
            'city' => '4',
            'website' => 'https://fnm',
            'wikiLink' => '',
            'slogan' => 'Go',
            'startDate' => 600000000,
            'endDate' => 0,
            'subGroupsSelector' => ['8'],
        ]);

        $fields = (new GroupFieldsDtoFactory())->fromSubmittedForm($form, ['title', 'abbreviation', 'type', 'country', 'city', 'website', 'wikiLink', 'slogan', 'startDate', 'endDate', 'subGroupsSelector']);

        self::assertSame('Fenomen', $fields->title);
        self::assertSame('FNM', $fields->abbreviation);
        self::assertSame('group', $fields->type);
        self::assertSame(3, $fields->countryId);
        self::assertSame(4, $fields->cityId);
        self::assertSame('https://fnm', $fields->website);
        self::assertSame('', $fields->wikiLink);
        self::assertSame('Go', $fields->slogan);
        self::assertSame(600000000, $fields->startDate);
        self::assertSame(0, $fields->endDate);
        self::assertSame([8], $fields->subGroupIds);
    }

    public function testFieldsTheFormDoesNotSubmitStayUntouched(): void
    {
        $fields = (new GroupFieldsDtoFactory())->fromSubmittedForm($this->formWith(['title' => 'X']), []);

        self::assertEquals(new GroupFieldsDto(), $fields);
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
