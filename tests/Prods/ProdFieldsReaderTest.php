<?php

declare(strict_types=1);

namespace ZxArt\Tests\Prods;

require_once __DIR__ . '/../Doubles/Elements/ZxProdElementSpy.php';

use groupElement;
use PHPUnit\Framework\TestCase;
use structureElement;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\LegalStatus;
use ZxArt\Prods\Services\ProdFieldsReader;
use ZxArt\Tests\Doubles\Elements\ZxProdElementSpy;
use zxProdElement;

class ProdFieldsReaderTest extends TestCase
{
    public function testReadAnswersEveryEditableFieldDecoded(): void
    {
        $prod = new ZxProdElementSpy(connectedCategoryIds: [92159, 92163]);
        $prod->values = [
            'title' => 'Rock &amp; Roll',
            'altTitle' => 'R&amp;R',
            'year' => '1999',
            'description' => '&lt;b&gt;demo&lt;/b&gt;',
            'htmlDescription' => '1',
            'instructions' => 'Press &quot;any&quot; key',
            'htmlInstructions' => '0',
            'legalStatus' => 'insales',
            'externalLink' => 'https://demo.invalid/',
            'youtubeId' => 'gmYoutube01',
            'party' => '2564',
            'partyplace' => '3',
            'compo' => 'demo &amp; intro',
            'denyVoting' => '1',
            'denyComments' => '0',
            'language' => ['en', 'ru'],
            'hardwareRequired' => ['zx48', 'ay'],
            'groups' => [$this->element(groupElement::class, 7)],
            'publishers' => [$this->element(groupElement::class, 8)],
            'compilationItems' => [$this->element(zxProdElement::class, 9)],
            'seriesProds' => [],
        ];
        $prod->tagTitles = ['Goldmaster-Tag-One'];

        self::assertEquals(new ProdFieldsDto(
            title: 'Rock & Roll',
            altTitle: 'R&R',
            year: 1999,
            description: '<b>demo</b>',
            htmlDescription: true,
            instructions: 'Press "any" key',
            htmlInstructions: false,
            legalStatus: LegalStatus::insales,
            externalLink: 'https://demo.invalid/',
            youtubeId: 'gmYoutube01',
            partyId: 2564,
            partyPlace: 3,
            compo: 'demo & intro',
            denyVoting: true,
            denyComments: false,
            languages: ['en', 'ru'],
            hardware: ['zx48', 'ay'],
            categoryIds: [92159, 92163],
            groupIds: [7],
            publisherIds: [8],
            tags: ['Goldmaster-Tag-One'],
            compilationItemIds: [9],
            seriesProdIds: [],
        ), (new ProdFieldsReader())->read($prod));
    }

    public function testAnUnknownStoredStatusAndEmptyListsReadAsNothingSet(): void
    {
        $prod = new ZxProdElementSpy();
        $prod->values = ['legalStatus' => ''];

        $fields = (new ProdFieldsReader())->read($prod);

        self::assertNull($fields->legalStatus);
        self::assertSame([], $fields->languages);
        self::assertSame([], $fields->groupIds);
        self::assertSame(0, $fields->partyId);
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
