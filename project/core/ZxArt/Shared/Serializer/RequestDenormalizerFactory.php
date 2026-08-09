<?php

declare(strict_types=1);

namespace ZxArt\Shared\Serializer;

use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Builds the serializer that turns JSON request bodies into typed request DTOs.
 *
 * A factory rather than a closure in `di-definitions.php` so tests exercise the
 * same configuration the application runs with.
 */
final class RequestDenormalizerFactory
{
    public static function create(): SerializerInterface
    {
        // PhpDocExtractor first: a native `array` type says nothing about its
        // values, so nested collections are resolved from the
        // `@param array<string, SomeDto>` docblock instead.
        $typeExtractor = new PropertyInfoExtractor(
            typeExtractors: [new PhpDocExtractor(), new ReflectionExtractor()],
        );

        return new Serializer(
            normalizers: [
                new BackedEnumNormalizer(),
                new ObjectNormalizer(propertyTypeExtractor: $typeExtractor),
                new ArrayDenormalizer(),
            ],
            encoders: [new JsonEncoder()],
        );
    }
}
