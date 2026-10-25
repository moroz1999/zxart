<?php

declare(strict_types=1);

namespace ZxArt\Tests\Releases;

require_once __DIR__ . '/../Doubles/Elements/ZxReleaseElementSpy.php';

use groupElement;
use PHPUnit\Framework\TestCase;
use structureElement;
use ZxArt\Releases\Dto\ReleaseFieldsDto;
use ZxArt\Releases\ReleaseTypes;
use ZxArt\Releases\Services\ReleaseFieldsReader;
use ZxArt\Tests\Doubles\Elements\ZxReleaseElementSpy;
use zxProdElement;

class ReleaseFieldsReaderTest extends TestCase
{
    public function testReadAnswersEveryEditableFieldDecoded(): void
    {
        $release = new ZxReleaseElementSpy();
        $release->values = [
            'title' => 'Rock &amp; Roll',
            'year' => '1999',
            'version' => 'v1.0 &quot;final&quot;',
            'description' => '&lt;b&gt;demo&lt;/b&gt;',
            'releaseType' => 'crack',
            'releaseFormat' => ['tap', 'tzx'],
            'language' => ['en'],
            'hardwareRequired' => ['ay'],
            'publishers' => [$this->element(groupElement::class, 7)],
            'zxProd' => [$this->element(zxProdElement::class, 9)],
            'denyVoting' => '1',
            'denyComments' => '0',
        ];

        self::assertEquals(new ReleaseFieldsDto(
            title: 'Rock & Roll',
            year: 1999,
            version: 'v1.0 "final"',
            description: '<b>demo</b>',
            releaseType: ReleaseTypes::crack,
            releaseFormats: ['tap', 'tzx'],
            languages: ['en'],
            hardware: ['ay'],
            publisherIds: [7],
            prodId: 9,
            denyVoting: true,
            denyComments: false,
        ), (new ReleaseFieldsReader())->read($release));
    }

    public function testAnUnknownStoredTypeAndEmptyListsReadAsNothingSet(): void
    {
        $release = new ZxReleaseElementSpy();
        $release->values = ['releaseType' => 'nonsense'];

        $fields = (new ReleaseFieldsReader())->read($release);

        self::assertNull($fields->releaseType);
        self::assertSame([], $fields->releaseFormats);
        self::assertSame([], $fields->publisherIds);
        self::assertSame(0, $fields->prodId);
    }

    /**
     * @param class-string<structureElement> $class
     */
    private function element(string $class, int $id): structureElement
    {
        $element = $this->createStub($class);
        $element->method('getId')->willReturn($id);

        return $element;
    }
}
