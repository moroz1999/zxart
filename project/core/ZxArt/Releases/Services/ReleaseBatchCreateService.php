<?php

declare(strict_types=1);

namespace ZxArt\Releases\Services;

use controller;
use structureElement;
use ZxArt\Forms\FormCreateService;
use ZxArt\Forms\FormCreateType;
use ZxArt\Forms\StagedUploadsCleaner;

/**
 * A production gains one release per uploaded file. The form is filled in once
 * and each release is created from it in turn, so every release carries the same
 * submitted values — the screenshots and inlays beside them included — and only
 * its release file differs.
 */
final readonly class ReleaseBatchCreateService
{
    public function __construct(
        private FormCreateService $formCreateService,
        private StagedUploadsCleaner $stagedUploadsCleaner,
    ) {
    }

    /**
     * @param array<string, mixed> $fields submitted values, `file` holding either
     *        one upload or the list the release form sends
     * @return non-empty-list<structureElement> the created releases, in upload order
     */
    public function create(controller $controller, array $fields, ?int $prodId): array
    {
        $releases = [];
        try {
            foreach ($this->splitByFile($fields) as $releaseFields) {
                $releases[] = $this->formCreateService->submit(
                    FormCreateType::Release,
                    null,
                    $controller,
                    $releaseFields,
                    $prodId,
                );
            }
        } finally {
            $this->clearStagedUploads($fields);
        }

        return $releases;
    }

    /**
     * Every release reads the same staged screenshots and inlays, so receiving
     * them cannot delete them. The batch is what knows the last release has read
     * them, whether it finished or gave up half way.
     *
     * @param array<string, mixed> $fields
     */
    private function clearStagedUploads(array $fields): void
    {
        foreach (array_keys($fields) as $name) {
            $this->stagedUploadsCleaner->clear($this->readUploadList($fields[$name]) ?? []);
        }
    }

    /**
     * One set of values per uploaded release file. A submit with a single file,
     * or none at all, still describes the one release the form was filled in for.
     *
     * @param array<string, mixed> $fields
     * @return non-empty-list<array<string, mixed>>
     */
    private function splitByFile(array $fields): array
    {
        $uploads = $this->readUploadList($fields['file'] ?? null);
        if ($uploads === null) {
            return [$fields];
        }

        $perFile = [];
        foreach ($uploads as $upload) {
            $perFile[] = [...$fields, 'file' => $upload];
        }

        return $perFile;
    }

    /**
     * The list PHP builds when several files arrive under one field name. A single
     * upload is its own set of properties instead, and a list of anything without
     * an uploaded file in it — release formats, languages, publishers — is not one.
     *
     * @return non-empty-list<array<array-key, mixed>>|null
     */
    private function readUploadList(mixed $value): ?array
    {
        if (!is_array($value) || $value === [] || !array_is_list($value)) {
            return null;
        }
        $uploads = [];
        foreach ($value as $upload) {
            if (!is_array($upload) || !isset($upload['tmp_name'])) {
                return null;
            }
            $uploads[] = $upload;
        }

        return $uploads;
    }
}
