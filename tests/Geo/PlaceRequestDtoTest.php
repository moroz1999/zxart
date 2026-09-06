<?php

declare(strict_types=1);

namespace ZxArt\Tests\Geo;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerException;
use Symfony\Component\Serializer\SerializerInterface;
use ZxArt\Geo\Dto\CitySaveDto;
use ZxArt\Geo\Dto\CountrySaveDto;
use ZxArt\Geo\Dto\PlaceDeleteDto;
use ZxArt\Shared\Serializer\RequestDenormalizerFactory;

/**
 * Denormalization of the country and city request bodies, through the same
 * denormalizer configuration the application is wired with.
 */
class PlaceRequestDtoTest extends TestCase
{
    private SerializerInterface $serializer;

    protected function setUp(): void
    {
        $this->serializer = RequestDenormalizerFactory::create();
    }

    public function testDenormalizesACompleteCountry(): void
    {
        $request = $this->serializer->deserialize(
            '{"id":100,"titles":{"930":"Россия","2105":"Russia"},"latitude":61.5,"longitude":105.3}',
            CountrySaveDto::class,
            'json',
        );

        $this->assertSame(100, $request->id);
        $this->assertSame([930 => 'Россия', 2105 => 'Russia'], $request->titles);
        $this->assertSame([61.5, 105.3], [$request->latitude, $request->longitude]);
    }

    public function testCoordinatesDefaultToTheNullIsland(): void
    {
        $request = $this->serializer->deserialize('{"titles":{}}', CountrySaveDto::class, 'json');

        $this->assertSame([0.0, 0.0], [$request->latitude, $request->longitude]);
    }

    public function testAWholeNumberCoordinateBecomesAFloat(): void
    {
        $request = $this->serializer->deserialize('{"titles":{},"latitude":10}', CountrySaveDto::class, 'json');

        $this->assertSame(10.0, $request->latitude);
    }

    public function testACityCarriesItsCountry(): void
    {
        $request = $this->serializer->deserialize(
            '{"countryId":100,"titles":{"2105":"Moscow"}}',
            CitySaveDto::class,
            'json',
        );

        $this->assertSame(100, $request->countryId);
        $this->assertNull($request->id);
    }

    public function testACityUpdateCarriesNoCountry(): void
    {
        $request = $this->serializer->deserialize('{"id":200,"titles":{}}', CitySaveDto::class, 'json');

        $this->assertNull($request->countryId);
    }

    public function testANonNumericCoordinateIsRejected(): void
    {
        $this->expectException(SerializerException::class);

        $this->serializer->deserialize('{"titles":{},"latitude":"north"}', CountrySaveDto::class, 'json');
    }

    public function testDeleteRequiresAnId(): void
    {
        $this->expectException(SerializerException::class);

        $this->serializer->deserialize('{}', PlaceDeleteDto::class, 'json');
    }
}
