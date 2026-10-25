<?php

declare(strict_types=1);

namespace ZxArt\Tests\Forms;

use PHPUnit\Framework\TestCase;
use structureElement;
use ZxArt\Forms\SubmittedFormValues;

final class SubmittedFormValuesTest extends TestCase
{
    public function testSubmittedValuesAreReadInTheirType(): void
    {
        $values = $this->valuesOf([
            'title' => 'Dizzy',
            'year' => '1987',
            'htmlDescription' => '1',
            'denyVoting' => '0',
            'groups' => ['5', '', 0, 6],
            'tags' => ['arcade', ''],
        ]);

        self::assertSame('Dizzy', $values->text('title'));
        self::assertSame(1987, $values->number('year'));
        self::assertTrue($values->flag('htmlDescription'));
        self::assertFalse($values->flag('denyVoting'));
        self::assertSame([5, 6], $values->ids('groups'));
        self::assertSame(['arcade'], $values->codes('tags'));
    }

    public function testSubmittedButEmptyValuesAreEmpty(): void
    {
        // an emptied field is posted as no value at all; it still clears the field
        $values = $this->valuesOf(['title' => null, 'year' => '', 'groups' => null, 'tags' => '']);

        self::assertSame('', $values->text('title'));
        self::assertSame(0, $values->number('year'));
        self::assertSame([], $values->ids('groups'));
        self::assertSame([], $values->codes('tags'));
    }

    public function testFieldsTheFormDoesNotSubmitAreNull(): void
    {
        $values = new SubmittedFormValues($this->formWith(['title' => 'Dizzy']), []);

        self::assertNull($values->text('title'));
        self::assertNull($values->number('year'));
        self::assertNull($values->flag('denyVoting'));
        self::assertNull($values->ids('groups'));
        self::assertNull($values->codes('tags'));
    }

    /**
     * @param array<string, mixed> $values
     */
    private function valuesOf(array $values): SubmittedFormValues
    {
        return new SubmittedFormValues($this->formWith($values), array_keys($values));
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
