<?php

declare(strict_types=1);

namespace ZxArt\Releases\Services;

use structureElement;
use ZxArt\Releases\Dto\ReleaseFieldsDto;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Releases\Repositories\ReleaseRepository;
use ZxArt\Shared\Elements\ElementFields;
use ZxArt\Shared\References\EntityReferenceResolver;
use ZxArt\Shared\References\InvalidReferenceException;
use ZxArt\Shared\References\ReferenceTypes;
use zxProdElement;
use zxReleaseElement;

/**
 * Writes the editable fields of a release. Only the fields the DTO carries are
 * touched, and every reference is checked before anything is written. A
 * production id other than the current one moves the release there.
 *
 * Checks no privilege and writes no log — the caller does.
 *
 * @psalm-api
 */
final readonly class ReleaseFieldsWriter
{
    public function __construct(
        private EntityReferenceResolver $entityReferenceResolver,
        private ReleaseFormatsProvider $releaseFormatsProvider,
        private ReleaseRepository $releaseRepository,
    ) {
    }

    public function apply(zxReleaseElement $release, ReleaseFieldsDto $fields): void
    {
        $references = $this->loadReferences($fields);

        $values = [
            'title' => $fields->title,
            'structureName' => $fields->title !== '' ? $fields->title : null,
            'year' => $fields->year,
            'version' => $fields->version,
            'description' => $fields->description,
            'releaseType' => $fields->releaseType?->value,
            'releaseFormat' => $fields->releaseFormats,
            'language' => $fields->languages,
            'hardwareRequired' => $fields->hardware,
            'denyVoting' => $fields->denyVoting,
            'denyComments' => $fields->denyComments,
            ...$references,
        ];
        ElementFields::assign($release, $values);
        $this->releaseRepository->save($release);
    }

    /**
     * @return array<string, structureElement[]> element field => loaded elements
     */
    private function loadReferences(ReleaseFieldsDto $fields): array
    {
        try {
            $this->entityReferenceResolver->assertHardwareCodes($fields->hardware ?? []);
            $this->entityReferenceResolver->assertLanguageCodes($fields->languages ?? []);
            $this->assertReleaseFormats($fields->releaseFormats ?? []);

            $references = [];
            if ($fields->publisherIds !== null) {
                $references['publishers'] = $this->entityReferenceResolver
                    ->elements($fields->publisherIds, ReferenceTypes::PUBLISHERS, 'publishers');
            }
            if ($fields->prodId !== null) {
                $references['zxProd'] = [
                    $this->entityReferenceResolver->element($fields->prodId, [zxProdElement::class], 'production'),
                ];
            }
        } catch (InvalidReferenceException $exception) {
            throw new ReleaseDataException($exception->getMessage(), 400, $exception);
        }

        return $references;
    }

    /**
     * @param string[] $formats
     */
    private function assertReleaseFormats(array $formats): void
    {
        $knownFormats = $this->releaseFormatsProvider->getReleaseFormats();
        foreach ($formats as $format) {
            if (!in_array($format, $knownFormats, true)) {
                throw new InvalidReferenceException("Unknown release format $format");
            }
        }
    }
}
