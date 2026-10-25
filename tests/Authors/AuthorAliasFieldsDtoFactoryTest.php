<?php

declare(strict_types=1);

namespace ZxArt\Tests\Authors;

use PHPUnit\Framework\TestCase;
use structureElement;
use ZxArt\Authors\Dto\AuthorAliasFieldsDto;
use ZxArt\Authors\Services\AuthorAliasFieldsDtoFactory;

final class AuthorAliasFieldsDtoFactoryTest extends TestCase
{
    public function testReadsEverySubmittedFieldInItsDomainType(): void
    {
        $form = $this->formWith([
            'title' => 'Fx',
            'authorId' => '7',
            'startDate' => 600000000,
            'endDate' => 0,
            'displayInMusic' => '1',
            'displayInGraphics' => '',
        ]);

        $fields = (new AuthorAliasFieldsDtoFactory())->fromSubmittedForm($form, ['title', 'authorId', 'startDate', 'endDate', 'displayInMusic', 'displayInGraphics']);

        self::assertSame('Fx', $fields->title);
        self::assertSame(7, $fields->authorId);
        self::assertSame(600000000, $fields->startDate);
        self::assertSame(0, $fields->endDate);
        self::assertTrue($fields->displayInMusic);
        self::assertFalse($fields->displayInGraphics);
    }

    public function testFieldsTheFormDoesNotSubmitStayUntouched(): void
    {
        $fields = (new AuthorAliasFieldsDtoFactory())->fromSubmittedForm($this->formWith(['title' => 'X']), []);

        self::assertEquals(new AuthorAliasFieldsDto(), $fields);
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
