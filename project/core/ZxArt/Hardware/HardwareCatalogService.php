<?php

declare(strict_types=1);

namespace ZxArt\Hardware;

use LanguagesManager;
use ZxArt\Hardware\Dto\HardwareItemDto;
use ZxArt\Hardware\Dto\HardwareNameDto;
use ZxArt\Hardware\Dto\HardwareNameInputDto;
use ZxArt\Hardware\Dto\HardwareSaveDto;
use ZxArt\Hardware\Exception\HardwareException;
use ZxArt\Hardware\Repositories\HardwareRepository;
use ZxArt\Shared\InterfaceLanguage;

/**
 * The editable hardware catalog: grouping, localized labels and code/id
 * translation for everything that stores hardware.
 *
 * The catalog is ~120 rows, so it is loaded once per request and served from
 * memory. Callers work in codes; only the link tables know about ids.
 */
final class HardwareCatalogService
{
    private const string CODE_PATTERN = '/^[a-z0-9_+]+$/';
    private const int MAX_CODE_LENGTH = 32;
    private const int MAX_NAME_LENGTH = 255;

    /** @var array<string, HardwareItemDto>|null code => item */
    private ?array $itemsByCode = null;

    public function __construct(
        private readonly HardwareRepository $repository,
        private readonly LanguagesManager $languagesManager,
    ) {
    }

    /**
     * @return array<string, HardwareItemDto> keyed by code, in catalog order
     */
    public function getItems(): array
    {
        if ($this->itemsByCode === null) {
            $this->itemsByCode = $this->loadItems();
        }

        return $this->itemsByCode;
    }

    public function getItemByCode(string $code): ?HardwareItemDto
    {
        return $this->getItems()[$code] ?? null;
    }

    /**
     * Grouped code lists in catalog order — the shape
     * {@see HardwareCatalog::getGroupedItems()} has always returned.
     *
     * @return array<string, list<string>> group value => codes
     */
    public function getGroupedCodes(): array
    {
        $grouped = [];
        foreach (HardwareGroup::cases() as $group) {
            $grouped[$group->value] = [];
        }
        foreach ($this->getItems() as $item) {
            $grouped[$item->category->value][] = $item->code;
        }

        return array_filter($grouped, static fn(array $codes): bool => $codes !== []);
    }

    public function getCategoryOf(string $code): ?HardwareGroup
    {
        return $this->getItemByCode($code)?->category;
    }

    /**
     * @param list<string> $codes
     * @return list<int> ids of the codes that exist, in the order given
     */
    public function getIdsByCodes(array $codes): array
    {
        $items = $this->getItems();
        $ids = [];
        foreach ($codes as $code) {
            $item = $items[$code] ?? null;
            if ($item !== null) {
                $ids[] = $item->id;
            }
        }

        return $ids;
    }

    /**
     * Labels for the given language, falling back to the code itself when a
     * translation row is missing, so a freshly added item is never blank.
     *
     * @return array<string, array{name: string, shortName: string}> keyed by code
     */
    public function getLabels(?InterfaceLanguage $language = null): array
    {
        $resolvedLanguage = $language ?? $this->getCurrentLanguage();
        $labels = [];
        foreach ($this->getItems() as $item) {
            $name = $item->getName($resolvedLanguage);
            $labels[$item->code] = [
                'name' => $name?->name ?? $item->code,
                'shortName' => $name?->shortName ?? $item->code,
            ];
        }

        return $labels;
    }

    /**
     * @throws HardwareException
     */
    public function create(HardwareSaveDto $request): HardwareItemDto
    {
        $normalizedCode = $this->validateCode($request->code);
        $validatedNames = $this->validateNames($request->names);

        if ($this->repository->findIdByCode($normalizedCode) !== null) {
            throw new HardwareException('Hardware code already exists: ' . $normalizedCode, 409);
        }

        $id = $this->repository->insert($normalizedCode, $request->category->value, $request->position);
        $this->repository->replaceNames($id, $validatedNames);
        $this->resetCache();

        return $this->readBack($normalizedCode);
    }

    /**
     * @throws HardwareException
     */
    public function update(HardwareSaveDto $request): HardwareItemDto
    {
        $id = $request->id
            ?? throw new HardwareException('Missing required field: id', 400);
        $normalizedCode = $this->validateCode($request->code);
        $validatedNames = $this->validateNames($request->names);

        if (!$this->repository->exists($id)) {
            throw new HardwareException('Hardware item not found', 404);
        }

        $existingId = $this->repository->findIdByCode($normalizedCode);
        if ($existingId !== null && $existingId !== $id) {
            throw new HardwareException('Hardware code already exists: ' . $normalizedCode, 409);
        }

        $this->repository->update($id, $normalizedCode, $request->category->value, $request->position);
        $this->repository->replaceNames($id, $validatedNames);
        $this->resetCache();

        return $this->readBack($normalizedCode);
    }

    /**
     * @throws HardwareException
     */
    private function readBack(string $code): HardwareItemDto
    {
        return $this->getItemByCode($code)
            ?? throw new HardwareException('Hardware item could not be read back', 500);
    }

    /**
     * @throws HardwareException
     */
    public function delete(int $id): void
    {
        if (!$this->repository->exists($id)) {
            throw new HardwareException('Hardware item not found', 404);
        }

        $usages = $this->repository->getUsageCounts()[$id] ?? 0;
        if ($usages > 0) {
            throw new HardwareException(
                'Hardware item is still used by ' . $usages . ' item(s) and cannot be deleted',
                409,
            );
        }

        $this->repository->delete($id);
        $this->resetCache();
    }

    /**
     * @return array<string, HardwareItemDto>
     */
    private function loadItems(): array
    {
        $namesByHardware = [];
        foreach ($this->repository->getAllNames() as $nameRow) {
            $language = InterfaceLanguage::tryFrom($nameRow['languageCode']);
            if ($language === null) {
                continue;
            }
            $namesByHardware[$nameRow['hardwareId']][$language->value] = new HardwareNameDto(
                language: $language,
                name: $nameRow['name'],
                shortName: $nameRow['shortName'],
            );
        }
        $usageCounts = $this->repository->getUsageCounts();

        $items = [];
        foreach ($this->repository->getAll() as $row) {
            $category = HardwareGroup::tryFrom($row['category']);
            if ($category === null) {
                continue;
            }
            $items[$row['code']] = new HardwareItemDto(
                id: $row['id'],
                code: $row['code'],
                category: $category,
                position: $row['position'],
                names: $namesByHardware[$row['id']] ?? [],
                usages: $usageCounts[$row['id']] ?? 0,
            );
        }

        return $items;
    }

    private function resetCache(): void
    {
        $this->itemsByCode = null;
    }

    /**
     * @throws HardwareException
     */
    private function validateCode(string $code): string
    {
        $normalizedCode = strtolower(trim($code));
        if ($normalizedCode === '') {
            throw new HardwareException('Hardware code is required');
        }
        if (strlen($normalizedCode) > self::MAX_CODE_LENGTH) {
            throw new HardwareException('Hardware code is longer than ' . self::MAX_CODE_LENGTH . ' characters');
        }
        if (preg_match(self::CODE_PATTERN, $normalizedCode) !== 1) {
            throw new HardwareException('Hardware code may only contain lowercase letters, digits, "_" and "+"');
        }

        return $normalizedCode;
    }

    /**
     * Every public language needs both labels: a missing one would show the bare
     * code to a whole audience.
     *
     * @param array<string, HardwareNameInputDto> $names keyed by language code
     * @return array<string, array{name: string, shortName: string}>
     * @throws HardwareException
     */
    private function validateNames(array $names): array
    {
        $validated = [];
        foreach ($this->getPublicLanguages() as $language) {
            $submitted = $names[$language->value] ?? null;
            $name = trim($submitted?->name ?? '');
            $shortName = trim($submitted?->shortName ?? '');
            if ($name === '' || $shortName === '') {
                throw new HardwareException('Both name and short name are required for language ' . $language->value);
            }
            if (strlen($name) > self::MAX_NAME_LENGTH || strlen($shortName) > self::MAX_NAME_LENGTH) {
                throw new HardwareException('Hardware name is longer than ' . self::MAX_NAME_LENGTH . ' characters');
            }
            $validated[$language->value] = ['name' => $name, 'shortName' => $shortName];
        }

        return $validated;
    }

    /**
     * The interface languages the site publishes, in the CMS's own order.
     *
     * @return list<InterfaceLanguage>
     */
    public function getPublicLanguages(): array
    {
        $languages = [];
        foreach ($this->languagesManager->getLanguagesList() as $language) {
            $interfaceLanguage = InterfaceLanguage::tryFrom((string)$language->iso6391);
            if ($interfaceLanguage !== null) {
                $languages[] = $interfaceLanguage;
            }
        }

        return $languages;
    }

    /**
     * The language of the current request. The CMS resolves it from the
     * `X-Language` header (iso6393) or the session; English is the fallback when
     * it cannot be resolved at all, so labels never come back blank.
     */
    public function getCurrentLanguage(): InterfaceLanguage
    {
        $currentLanguage = $this->languagesManager->getCurrentLanguage();
        if (is_object($currentLanguage)) {
            $interfaceLanguage = InterfaceLanguage::tryFrom((string)$currentLanguage->iso6391);
            if ($interfaceLanguage !== null) {
                return $interfaceLanguage;
            }
        }

        return InterfaceLanguage::En;
    }
}
