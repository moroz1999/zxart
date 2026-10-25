<?php

declare(strict_types=1);

namespace ZxArt\Tests\Releases;

use PHPUnit\Framework\TestCase;
use structureElement;
use ZxArt\Releases\ReleaseTypes;
use ZxArt\Releases\Services\ReleaseFieldsDtoFactory;

final class ReleaseFieldsDtoFactoryTest extends TestCase
{
    private const array RELEASE_FORM_FIELDS = [
        'title', 'year', 'version', 'description', 'denyVoting', 'denyComments', 'releaseType',
        'releaseFormat', 'language', 'hardwareRequired', 'publishers', 'zxProd',
    ];

    public function testReadsEverySubmittedFieldInItsDomainType(): void
    {
        $form = $this->formWith([
            'title' => 'Dizzy (crack)',
            'year' => '1988',
            'version' => '1.1',
            'description' => '<p>Cracked</p>',
            'denyVoting' => '1',
            'denyComments' => '0',
            'releaseType' => 'crack',
            'releaseFormat' => ['tap'],
            'language' => ['en'],
            'hardwareRequired' => ['zx48'],
            'publishers' => ['5'],
            'zxProd' => ['9'],
        ]);

        $fields = (new ReleaseFieldsDtoFactory())->fromSubmittedForm($form, self::RELEASE_FORM_FIELDS);

        self::assertSame('Dizzy (crack)', $fields->title);
        self::assertSame(1988, $fields->year);
        self::assertSame('1.1', $fields->version);
        self::assertSame('<p>Cracked</p>', $fields->description);
        self::assertTrue($fields->denyVoting);
        self::assertFalse($fields->denyComments);
        self::assertSame(ReleaseTypes::crack, $fields->releaseType);
        self::assertSame(['tap'], $fields->releaseFormats);
        self::assertSame(['en'], $fields->languages);
        self::assertSame(['zx48'], $fields->hardware);
        self::assertSame([5], $fields->publisherIds);
        self::assertSame(9, $fields->prodId);
    }

    public function testFieldsTheFormDoesNotSubmitStayUntouched(): void
    {
        $fields = (new ReleaseFieldsDtoFactory())->fromSubmittedForm($this->formWith(['year' => '1988']), ['year']);

        self::assertNull($fields->title);
        self::assertNull($fields->prodId);
        self::assertNull($fields->releaseType);
    }

    public function testUnknownReleaseTypeLeavesItUntouched(): void
    {
        $fields = (new ReleaseFieldsDtoFactory())->fromSubmittedForm($this->formWith(['releaseType' => '']), ['releaseType']);

        self::assertNull($fields->releaseType);
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
