<?php

declare(strict_types=1);

namespace ZxArt\Tests\Prods;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use zxProdElement;
use ZxArt\Prods\ProdDescriptionService;
use ZxArt\Prods\ProdElementService;
use ZxArt\Shared\DescriptionFormatter;

/**
 * A production's description and instructions each carry their own format flag,
 * and both texts are decoded the same way.
 */
#[AllowMockObjectsWithoutExpectations]
class ProdDescriptionServiceTest extends TestCase
{
    public function testEachTextKeepsItsOwnFormat(): void
    {
        $dto = $this->serviceFor(false, true, 'About', 'Press &lt;b&gt;fire&lt;/b&gt;')->getDescription(1);

        $this->assertFalse($dto->htmlDescription);
        $this->assertTrue($dto->htmlInstructions);
        $this->assertSame('Press <b>fire</b>', $dto->instructions);
    }

    public function testPlainInstructionsAreDecodedLikeTheDescription(): void
    {
        $dto = $this->serviceFor(true, false, '&amp;amp;', 'Q - up &amp;amp; A - down')->getDescription(1);

        $this->assertTrue($dto->htmlDescription);
        $this->assertFalse($dto->htmlInstructions);
        $this->assertSame('&', $dto->description);
        $this->assertSame('Q - up & A - down', $dto->instructions);
    }

    private function serviceFor(
        bool $htmlDescription,
        bool $htmlInstructions,
        string $description,
        string $instructions,
    ): ProdDescriptionService {
        $element = $this->createMock(zxProdElement::class);
        $element->method('isHtmlDescription')->willReturn($htmlDescription);
        $element->method('isHtmlInstructions')->willReturn($htmlInstructions);
        $element->method('getDescription')->willReturn($description);
        $element->method('getInstructions')->willReturn($instructions);

        $elementService = $this->createMock(ProdElementService::class);
        $elementService->method('get')->willReturn($element);

        return new ProdDescriptionService($elementService, new DescriptionFormatter());
    }
}
