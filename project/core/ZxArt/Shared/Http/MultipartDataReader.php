<?php

declare(strict_types=1);

namespace ZxArt\Shared\Http;

use InvalidArgumentException;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerException;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Reads the JSON `data` part of a multipart request — the request beside its
 * files — into its request DTO. A missing or malformed part is refused.
 *
 * @psalm-api
 */
final readonly class MultipartDataReader
{
    public function __construct(
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * @template T of object
     * @param array<array-key, mixed> $post the form fields, `$_POST`
     * @param class-string<T> $requestClass
     * @return T
     * @throws InvalidArgumentException
     */
    public function read(array $post, string $requestClass): object
    {
        $data = $post['data'] ?? null;
        if (!is_string($data) || $data === '') {
            throw new InvalidArgumentException('The request carries no data part');
        }
        try {
            $request = $this->serializer->deserialize($data, $requestClass, 'json');
        } catch (SerializerException $exception) {
            throw new InvalidArgumentException($exception->getMessage(), 0, $exception);
        }
        if (!$request instanceof $requestClass) {
            throw new InvalidArgumentException('The data part is no ' . $requestClass);
        }

        return $request;
    }
}
