<?php
declare(strict_types=1);

namespace ZxArt\Import\Prods\Dto;

use ZxArt\Import\Labels\Label;

readonly final class ReleaseImportDTO
{
    public function __construct(
        public string   $id,
        public string   $title,
        public ?int     $year = null,
        /** @var string[]|null */
        public ?array   $language = null,
        public ?string  $version = null,
        public ?string  $releaseType = null,
        public ?string  $filePath = null,
        public ?string  $fileUrl = null,
        public ?string  $fileName = null,
        public ?string  $description = null,
        /** @var string[]|null */
        public ?array   $hardwareRequired = null,
        /** @var Label[]|null */
        public ?array   $labels = null,
        /** @var array<string,string[]>|null */
        public ?array   $authors = null,
        /** @var string[]|null */
        public ?array   $publishers = null,
        /** @var array<string,string[]>|null */
        public ?array   $undetermined = null,
        /** @var string[]|null */
        public ?array   $images = null,
        /** @var string[]|null */
        public ?array   $inlayImages = null,
        /** @var string[]|null */
        public ?array   $infoFiles = null,
        /** @var string[]|null */
        public ?array   $adFiles = null,
        public ?string  $md5 = null,
    ) {}

    public static function fromArray(array $a): self
    {
        $labels = isset($a['labels'])
            ? array_map(fn($x) => Label::fromArray($x), (array)$a['labels'])
            : null;

        return new self(
            id: (string)$a['id'],
            title: (string)($a['title'] ?? ''),
            year: isset($a['year']) && $a['year'] !== '' ? (int)$a['year'] : null,
            language: isset($a['language']) ? (array)$a['language'] : null,
            version: $a['version'] ?? null,
            releaseType: $a['releaseType'] ?? null,
            filePath: $a['filePath'] ?? null,
            fileUrl: $a['fileUrl'] ?? null,
            fileName: $a['fileName'] ?? null,
            description: $a['description'] ?? null,
            hardwareRequired: isset($a['hardwareRequired']) ? array_values((array)$a['hardwareRequired']) : null,
            labels: $labels,
            authors: isset($a['authors']) ? (array)$a['authors'] : null,
            publishers: isset($a['publishers']) ? array_values((array)$a['publishers']) : null,
            undetermined: isset($a['undetermined']) ? (array)$a['undetermined'] : null,
            images: isset($a['images']) ? array_values((array)$a['images']) : null,
            inlayImages: isset($a['inlayImages']) ? array_values((array)$a['inlayImages']) : null,
            infoFiles: isset($a['infoFiles']) ? array_values((array)$a['infoFiles']) : null,
            adFiles: isset($a['adFiles']) ? array_values((array)$a['adFiles']) : null,
            md5: $a['md5'] ?? null,
        );
    }
}
