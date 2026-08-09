<?php

declare(strict_types=1);

namespace ZxArt\Shared\ObjectMapper;

use BackedEnum;
use Symfony\Component\ObjectMapper\TransformCallableInterface;

/**
 * Maps a backed enum property to its scalar value.
 *
 * Internal DTOs carry real enums; REST DTOs carry the wire representation, and
 * ObjectMapper passes values through untouched without a transform.
 *
 * @implements TransformCallableInterface<object, object>
 */
final class BackedEnumValue implements TransformCallableInterface
{
    public function __invoke(mixed $value, object $source, ?object $target): mixed
    {
        return $value instanceof BackedEnum ? $value->value : $value;
    }
}
