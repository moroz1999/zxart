<?php

declare(strict_types=1);

namespace ZxArt\Tests\ZxProdCategories;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerException;
use Symfony\Component\Serializer\SerializerInterface;
use ZxArt\Shared\Serializer\RequestDenormalizerFactory;
use ZxArt\ZxProdCategories\Dto\CategoryDeleteDto;
use ZxArt\ZxProdCategories\Dto\CategorySaveDto;

/**
 * Denormalization of the category request bodies, through the same denormalizer
 * configuration the application is wired with.
 */
class CategoryRequestDtoTest extends TestCase
{
    private SerializerInterface $serializer;

    protected function setUp(): void
    {
        $this->serializer = RequestDenormalizerFactory::create();
    }

    public function testDenormalizesACreationUnderAParent(): void
    {
        $request = $this->save(['parentId' => 10, 'titles' => ['930' => 'Игры', '2105' => 'Games']]);

        $this->assertNull($request->id);
        $this->assertSame(10, $request->parentId);
        $this->assertSame([930 => 'Игры', 2105 => 'Games'], $request->titles);
    }

    public function testATopLevelCreationCarriesNoParent(): void
    {
        $this->assertNull($this->save(['titles' => ['930' => 'Игры']])->parentId);
    }

    public function testKeepsTheIdWhenGiven(): void
    {
        $this->assertSame(42, $this->save(['id' => 42, 'titles' => []])->id);
    }

    public function testABodyWithNoTitlesDenormalizesToAnEmptyMap(): void
    {
        $this->assertSame([], $this->save([])->titles);
    }

    public function testANonNumericIdIsRejected(): void
    {
        $this->expectException(SerializerException::class);

        $this->save(['id' => 'ten', 'titles' => []]);
    }

    public function testDeleteRequiresAnId(): void
    {
        $this->expectException(SerializerException::class);

        $this->serializer->deserialize('{}', CategoryDeleteDto::class, 'json');
    }

    public function testDeleteCarriesTheId(): void
    {
        $request = $this->serializer->deserialize('{"id":7}', CategoryDeleteDto::class, 'json');

        $this->assertSame(7, $request->id);
    }

    /**
     * @param array<string, mixed> $body
     */
    private function save(array $body): CategorySaveDto
    {
        return $this->serializer->deserialize((string)json_encode($body), CategorySaveDto::class, 'json');
    }
}
