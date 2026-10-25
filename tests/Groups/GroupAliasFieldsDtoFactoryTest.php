<?php

declare(strict_types=1);

namespace ZxArt\Tests\Groups;

use PHPUnit\Framework\TestCase;
use structureElement;
use ZxArt\Groups\Dto\GroupAliasFieldsDto;
use ZxArt\Groups\Services\GroupAliasFieldsDtoFactory;

final class GroupAliasFieldsDtoFactoryTest extends TestCase
{
    public function testReadsEverySubmittedFieldInItsDomainType(): void
    {
        $form = $this->formWith([
            'title' => 'FNM',
            'groupId' => '5',
            'startDate' => 600000000,
            'endDate' => 0,
        ]);

        $fields = (new GroupAliasFieldsDtoFactory())->fromSubmittedForm($form, ['title', 'groupId', 'startDate', 'endDate']);

        self::assertSame('FNM', $fields->title);
        self::assertSame(5, $fields->groupId);
        self::assertSame(600000000, $fields->startDate);
        self::assertSame(0, $fields->endDate);
    }

    public function testFieldsTheFormDoesNotSubmitStayUntouched(): void
    {
        $fields = (new GroupAliasFieldsDtoFactory())->fromSubmittedForm($this->formWith(['title' => 'X']), []);

        self::assertEquals(new GroupAliasFieldsDto(), $fields);
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
