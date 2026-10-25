<?php

declare(strict_types=1);

namespace ZxArt\Shared\References;

use structureElement;
use structureManager;
use ZxArt\Hardware\HardwareCatalogService;
use ZxArt\Shared\LanguageCodes;

/**
 * Resolves the references an entity write carries — ids of other elements,
 * hardware and language codes — and refuses one that names nothing or the wrong
 * kind of thing, before anything is written.
 *
 * @psalm-api
 */
final readonly class EntityReferenceResolver
{
    public function __construct(
        private structureManager $structureManager,
        private HardwareCatalogService $hardwareCatalogService,
    ) {
    }

    /**
     * @template T of structureElement
     * @param list<class-string<T>> $types
     * @return T
     */
    public function element(int $id, array $types, string $field): structureElement
    {
        $element = $this->structureManager->getElementById($id);
        foreach ($types as $type) {
            if ($element instanceof $type) {
                return $element;
            }
        }

        throw new InvalidReferenceException("Element $id cannot be used as $field");
    }

    /**
     * @template T of structureElement
     * @param int[] $ids
     * @param list<class-string<T>> $types
     * @return list<T>
     */
    public function elements(array $ids, array $types, string $field): array
    {
        return array_values(array_map(fn(int $id): structureElement => $this->element($id, $types, $field), $ids));
    }

    /**
     * @param string[] $codes
     */
    public function assertHardwareCodes(array $codes): void
    {
        foreach ($codes as $code) {
            if ($this->hardwareCatalogService->getItemByCode($code) === null) {
                throw new InvalidReferenceException("Unknown hardware code $code");
            }
        }
    }

    /**
     * @param string[] $codes
     */
    public function assertLanguageCodes(array $codes): void
    {
        foreach ($codes as $code) {
            if (!in_array($code, LanguageCodes::ALL, true)) {
                throw new InvalidReferenceException("Unknown language code $code");
            }
        }
    }
}
