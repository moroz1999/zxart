<?php

declare(strict_types=1);

namespace ZxArt\Tests\Hardware;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerException;
use Symfony\Component\Serializer\SerializerInterface;
use ZxArt\Hardware\Dto\HardwareDeleteDto;
use ZxArt\Hardware\Dto\HardwareNameInputDto;
use ZxArt\Hardware\Dto\HardwareSaveDto;
use ZxArt\Hardware\HardwareGroup;
use ZxArt\Shared\Serializer\RequestDenormalizerFactory;

/**
 * Denormalization of the hardware request bodies, through the same denormalizer
 * configuration the application is wired with.
 */
class HardwareSaveDtoTest extends TestCase
{
    private SerializerInterface $serializer;

    protected function setUp(): void
    {
        $this->serializer = RequestDenormalizerFactory::create();
    }

    public function testDenormalizesACompleteBody(): void
    {
        $request = $this->denormalize($this->body());

        $this->assertNull($request->id);
        $this->assertSame('mb02', $request->code);
        $this->assertSame(HardwareGroup::STORAGE, $request->category);
        $this->assertSame(57, $request->position);
        $this->assertSame(['en', 'ru', 'es'], array_keys($request->names));
        $this->assertInstanceOf(HardwareNameInputDto::class, $request->names['ru']);
        $this->assertSame('MB-02+', $request->names['ru']->name);
        $this->assertSame('MB-02', $request->names['ru']->shortName);
    }

    public function testKeepsTheIdWhenGiven(): void
    {
        $this->assertSame(42, $this->denormalize([...$this->body(), 'id' => 42])->id);
    }

    public function testOptionalFieldsFallBackToTheirDefaults(): void
    {
        $request = $this->denormalize(['code' => 'mb02', 'category' => 'dos']);

        $this->assertNull($request->id);
        $this->assertSame(0, $request->position);
        $this->assertSame([], $request->names);
    }

    public function testUnknownCategoryIsRejected(): void
    {
        $this->expectException(SerializerException::class);

        $this->denormalize([...$this->body(), 'category' => 'peripherals']);
    }

    public function testMissingCategoryIsRejected(): void
    {
        $this->expectException(SerializerException::class);

        $this->denormalize(['code' => 'mb02']);
    }

    /**
     * An unknown language key is not a malformed request — the catalog service
     * decides which languages an item must cover, and reports the missing ones.
     */
    public function testAnUnknownLanguageKeyIsCarriedThrough(): void
    {
        $request = $this->denormalize([
            ...$this->body(),
            'names' => ['de' => ['name' => 'X', 'shortName' => 'X']],
        ]);

        $this->assertSame(['de'], array_keys($request->names));
    }

    public function testDeleteRequestRequiresAnId(): void
    {
        $this->assertSame(7, $this->serializer->deserialize('{"id":7}', HardwareDeleteDto::class, 'json')->id);

        $this->expectException(SerializerException::class);
        $this->serializer->deserialize('{}', HardwareDeleteDto::class, 'json');
    }

    /**
     * @param array<string, mixed> $body
     */
    private function denormalize(array $body): HardwareSaveDto
    {
        return $this->serializer->deserialize(json_encode($body, JSON_THROW_ON_ERROR), HardwareSaveDto::class, 'json');
    }

    /**
     * @return array<string, mixed>
     */
    private function body(): array
    {
        return [
            'code' => 'mb02',
            'category' => 'storage',
            'position' => 57,
            'names' => [
                'en' => ['name' => 'MB-02+', 'shortName' => 'MB-02'],
                'ru' => ['name' => 'MB-02+', 'shortName' => 'MB-02'],
                'es' => ['name' => 'MB-02+', 'shortName' => 'MB-02'],
            ],
        ];
    }
}
