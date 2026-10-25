<?php

declare(strict_types=1);

namespace ZxArt\Tests\Prods;

use PHPUnit\Framework\TestCase;
use structureElement;
use ZxArt\Prods\LegalStatus;
use ZxArt\Prods\Services\ProdFieldsDtoFactory;

final class ProdFieldsDtoFactoryTest extends TestCase
{
    private const array PROD_FORM_FIELDS = [
        'title', 'altTitle', 'party', 'partyplace', 'compo', 'year', 'youtubeId', 'publishers', 'groups',
        'htmlDescription', 'htmlInstructions', 'description', 'instructions', 'tags', 'denyVoting',
        'denyComments', 'legalStatus', 'language', 'hardwareRequired', 'categories', 'compilationItems',
        'seriesProds', 'externalLink',
    ];

    public function testReadsEverySubmittedFieldInItsDomainType(): void
    {
        $form = $this->formWith([
            'altTitle' => 'Dizzy I',
            'year' => '1987',
            'description' => '<p>An egg</p>',
            'htmlDescription' => '1',
            'instructions' => '',
            'htmlInstructions' => '0',
            'legalStatus' => 'allowedzxart',
            'externalLink' => 'https://example.com',
            'youtubeId' => 'abc',
            'party' => '9',
            'partyplace' => '2',
            'compo' => 'demo',
            'denyVoting' => '0',
            'denyComments' => '1',
            'language' => ['en'],
            'hardwareRequired' => ['zx48'],
            'categories' => ['3', '4'],
            'groups' => ['5'],
            'publishers' => [6],
            'tags' => ['arcade'],
            'compilationItems' => ['7'],
            'seriesProds' => [],
        ]);

        $fields = (new ProdFieldsDtoFactory())->fromSubmittedForm($form, self::PROD_FORM_FIELDS, 'Dizzy');

        self::assertSame('Dizzy', $fields->title);
        self::assertSame('Dizzy I', $fields->altTitle);
        self::assertSame(1987, $fields->year);
        self::assertSame('<p>An egg</p>', $fields->description);
        self::assertTrue($fields->htmlDescription);
        self::assertSame('', $fields->instructions);
        self::assertFalse($fields->htmlInstructions);
        self::assertSame(LegalStatus::allowedzxart, $fields->legalStatus);
        self::assertSame('https://example.com', $fields->externalLink);
        self::assertSame('abc', $fields->youtubeId);
        self::assertSame(9, $fields->partyId);
        self::assertSame(2, $fields->partyPlace);
        self::assertSame('demo', $fields->compo);
        self::assertFalse($fields->denyVoting);
        self::assertTrue($fields->denyComments);
        self::assertSame(['en'], $fields->languages);
        self::assertSame(['zx48'], $fields->hardware);
        self::assertSame([3, 4], $fields->categoryIds);
        self::assertSame([5], $fields->groupIds);
        self::assertSame([6], $fields->publisherIds);
        self::assertSame(['arcade'], $fields->tags);
        self::assertSame([7], $fields->compilationItemIds);
        self::assertSame([], $fields->seriesProdIds);
    }

    public function testFieldsTheFormDoesNotSubmitStayUntouched(): void
    {
        // the batch upload form carries no compilation items and no series
        $fields = (new ProdFieldsDtoFactory())->fromSubmittedForm($this->formWith(['year' => '1987']), ['year'], 'Dizzy');

        self::assertSame(1987, $fields->year);
        self::assertNull($fields->altTitle);
        self::assertNull($fields->compilationItemIds);
        self::assertNull($fields->seriesProdIds);
    }

    public function testSubmittedButEmptyFieldsClearTheirValue(): void
    {
        // an emptied multi-select is posted as no value at all
        $form = $this->formWith(['groups' => null, 'tags' => '', 'externalLink' => null, 'party' => '']);

        $fields = (new ProdFieldsDtoFactory())->fromSubmittedForm(
            $form,
            ['groups', 'tags', 'externalLink', 'party'],
            'Dizzy',
        );

        self::assertSame([], $fields->groupIds);
        self::assertSame([], $fields->tags);
        self::assertSame('', $fields->externalLink);
        self::assertSame(0, $fields->partyId);
    }

    public function testAltTitleCanComeFromAFieldOfAnotherName(): void
    {
        $fields = (new ProdFieldsDtoFactory())->fromSubmittedForm(
            $this->formWith(['prodAltTitle' => 'Dizzy I']),
            ['prodAltTitle'],
            'Dizzy',
            altTitleField: 'prodAltTitle',
        );

        self::assertSame('Dizzy I', $fields->altTitle);
    }

    public function testUnknownLegalStatusLeavesItUntouched(): void
    {
        $fields = (new ProdFieldsDtoFactory())->fromSubmittedForm($this->formWith(['legalStatus' => '']), ['legalStatus'], 'Dizzy');

        self::assertNull($fields->legalStatus);
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
