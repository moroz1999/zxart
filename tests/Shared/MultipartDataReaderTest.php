<?php

declare(strict_types=1);

namespace ZxArt\Tests\Shared;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\Dto\ProdSaveRequestDto;
use ZxArt\Shared\Http\MultipartDataReader;
use ZxArt\Shared\Serializer\RequestDenormalizerFactory;

/**
 * The JSON `data` part of a multipart request, read into its request DTO.
 */
final class MultipartDataReaderTest extends TestCase
{
    private MultipartDataReader $reader;

    protected function setUp(): void
    {
        $this->reader = new MultipartDataReader(RequestDenormalizerFactory::create());
    }

    public function testTheDataPartBecomesTheRequest(): void
    {
        $request = $this->reader->read(['data' => '{"fields":{"title":"Goldmaster Demo"},"authors":[]}'], ProdSaveRequestDto::class);

        self::assertEquals(new ProdSaveRequestDto(new ProdFieldsDto(title: 'Goldmaster Demo'), authors: []), $request);
    }

    public function testAMissingDataPartIsRefused(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->reader->read([], ProdSaveRequestDto::class);
    }

    public function testAMalformedDataPartIsRefused(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->reader->read(['data' => '{"fields":{"year":"soon"}}'], ProdSaveRequestDto::class);
    }
}
