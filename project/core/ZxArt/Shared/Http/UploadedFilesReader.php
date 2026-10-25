<?php

declare(strict_types=1);

namespace ZxArt\Shared\Http;

use InvalidArgumentException;
use ZxArt\Shared\Dto\UploadedFileDto;

/**
 * Reads the files of a multipart request from what PHP puts in `$_FILES`: one
 * file under a name, a list under `name[]`, or lists by kind under
 * `name[kind][]`. An empty slot is no file; an upload that failed is refused.
 *
 * @psalm-api
 */
final readonly class UploadedFilesReader
{
    /**
     * @param array<array-key, mixed> $files
     * @throws InvalidArgumentException
     */
    public function one(array $files, string $name): ?UploadedFileDto
    {
        $entry = $files[$name] ?? null;
        if (!is_array($entry) || is_array($entry['name'] ?? null)) {
            return null;
        }

        return $this->file($entry['name'] ?? '', $entry['tmp_name'] ?? '', $entry['error'] ?? UPLOAD_ERR_NO_FILE);
    }

    /**
     * @param array<array-key, mixed> $files
     * @return list<UploadedFileDto>
     * @throws InvalidArgumentException
     */
    public function many(array $files, string $name): array
    {
        $entry = $files[$name] ?? null;
        if (!is_array($entry) || !is_array($entry['name'] ?? null)) {
            return [];
        }

        return $this->list($entry['name'], (array)($entry['tmp_name'] ?? []), (array)($entry['error'] ?? []));
    }

    /**
     * @param array<array-key, mixed> $files
     * @return array<string, list<UploadedFileDto>> kind => its files
     * @throws InvalidArgumentException
     */
    public function byKind(array $files, string $name): array
    {
        $entry = $files[$name] ?? null;
        if (!is_array($entry) || !is_array($entry['name'] ?? null)) {
            return [];
        }
        $byKind = [];
        foreach ($entry['name'] as $kind => $names) {
            if (!is_array($names)) {
                continue;
            }
            $kindFiles = $this->list(
                $names,
                (array)($entry['tmp_name'][$kind] ?? []),
                (array)($entry['error'][$kind] ?? []),
            );
            if ($kindFiles !== []) {
                $byKind[(string)$kind] = $kindFiles;
            }
        }

        return $byKind;
    }

    /**
     * @param array<array-key, mixed> $names
     * @param array<array-key, mixed> $tmpNames
     * @param array<array-key, mixed> $errors
     * @return list<UploadedFileDto>
     */
    private function list(array $names, array $tmpNames, array $errors): array
    {
        $list = [];
        foreach ($names as $index => $fileName) {
            $file = $this->file($fileName, $tmpNames[$index] ?? '', $errors[$index] ?? UPLOAD_ERR_NO_FILE);
            if ($file !== null) {
                $list[] = $file;
            }
        }

        return $list;
    }

    private function file(mixed $fileName, mixed $tmpName, mixed $error): ?UploadedFileDto
    {
        if ((int)$error === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ((int)$error !== UPLOAD_ERR_OK || !is_string($tmpName) || $tmpName === '') {
            throw new InvalidArgumentException('The upload of ' . (is_string($fileName) ? $fileName : 'a file') . ' failed');
        }

        return new UploadedFileDto($tmpName, is_string($fileName) ? basename($fileName) : 'file');
    }
}
