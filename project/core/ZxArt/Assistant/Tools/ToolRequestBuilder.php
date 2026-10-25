<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools;

use JsonException;
use ReflectionClass;
use ReflectionParameter;
use stdClass;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Builds a tool's request DTO from the JSON arguments of a call, with the
 * deserializer the REST request bodies use. A top-level field the DTO does not
 * have is refused too: the model is told rather than silently ignored.
 *
 * @psalm-api
 */
final readonly class ToolRequestBuilder
{
    public function __construct(
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * @template TRequest of object
     * @param class-string<TRequest> $requestClass
     * @return TRequest
     * @throws ToolArgumentsException
     */
    public function build(string $requestClass, string $arguments): object
    {
        try {
            $decoded = json_decode($arguments, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new ToolArgumentsException('The arguments are not valid JSON: ' . $exception->getMessage(), 0, $exception);
        }
        if (!$decoded instanceof stdClass) {
            throw new ToolArgumentsException('The arguments must be a JSON object');
        }
        $unknownFields = array_diff(array_keys(get_object_vars($decoded)), $this->fieldNames($requestClass));
        if ($unknownFields !== []) {
            throw new ToolArgumentsException('Unknown fields: ' . implode(', ', $unknownFields));
        }

        try {
            return $this->serializer->deserialize($arguments, $requestClass, 'json');
        } catch (SerializerException $exception) {
            throw new ToolArgumentsException($this->messageOf($exception), 0, $exception);
        }
    }

    /**
     * Some messages leave the field out (a value outside an enum); the path
     * names it.
     */
    private function messageOf(SerializerException $exception): string
    {
        $path = $exception instanceof NotNormalizableValueException ? $exception->getPath() : null;
        $message = $exception->getMessage();

        return $path !== null && $path !== '' && !str_contains($message, $path) ? "$path: $message" : $message;
    }

    /**
     * @param class-string $requestClass
     * @return string[]
     */
    private function fieldNames(string $requestClass): array
    {
        $parameters = (new ReflectionClass($requestClass))->getConstructor()?->getParameters() ?? [];

        return array_map(static fn(ReflectionParameter $parameter): string => $parameter->getName(), $parameters);
    }
}
