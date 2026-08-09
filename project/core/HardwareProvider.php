<?php
declare(strict_types=1);

use ZxArt\Hardware\HardwareCatalog;
use ZxArt\Hardware\HardwareCatalogService;

trait HardwareProvider
{
    public function getHardwareList(): array
    {
        return $this->getService(HardwareCatalog::class)->getGroupedItems();
    }

    public function getHardwareType(string $item): ?string
    {
        return $this->getService(HardwareCatalog::class)->getItemGroup($item)?->value;
    }

    /**
     * Ready-made form options: every catalog code with its full label and the
     * group it belongs to, so a form can render them grouped without knowing a
     * single hardware name itself.
     *
     * @return list<array{value: string, label: string, group: string}>
     */
    public function getHardwareOptions(): array
    {
        $labels = $this->getService(HardwareCatalogService::class)->getLabels();

        $options = [];
        foreach ($this->getHardwareList() as $groupName => $groupCodes) {
            foreach ($groupCodes as $code) {
                $options[] = [
                    'value' => $code,
                    'label' => $labels[$code]['name'] ?? $code,
                    'group' => $groupName,
                ];
            }
        }

        return $options;
    }

    /**
     * The element's **own** hardware with labels — what an edit form binds to and
     * what every page prints, card and detail alike. Nothing displays a wider set:
     * a production is described by what its releases share, and the aggregate is
     * for matching, not for labelling. See docs/domain/hardware.md.
     *
     * @return list<array{id: string, name: string, shortName: string, category: string}>
     */
    public function getHardwareDetails(): array
    {
        return $this->buildHardwareDetails($this->getHardwareCodes());
    }

    /**
     * Everything the element runs on, with labels — a release's own codes plus
     * what it inherits, a production's own plus its releases'.
     *
     * For a **release** this is what every view uses: it records only its
     * deviations, so its own set is empty for most of the catalogue. For a
     * production nothing displays this; a production is shown its own set. See
     * docs/domain/hardware.md.
     *
     * @return list<array{id: string, name: string, shortName: string, category: string}>
     */
    public function getRunsOnHardwareDetails(): array
    {
        return $this->buildHardwareDetails($this->getRunsOnHardwareCodes());
    }

    /**
     * Labels for the request language; a code the catalog no longer knows falls
     * back to itself rather than rendering blank.
     *
     * @param string[] $codes
     * @return list<array{id: string, name: string, shortName: string, category: string}>
     */
    private function buildHardwareDetails(array $codes): array
    {
        $catalogService = $this->getService(HardwareCatalogService::class);
        $labels = $catalogService->getLabels();

        $details = [];
        foreach ($codes as $code) {
            $label = $labels[$code] ?? null;
            $details[] = [
                'id' => $code,
                'name' => $label['name'] ?? $code,
                'shortName' => $label['shortName'] ?? $code,
                'category' => $catalogService->getCategoryOf($code)?->value ?? '',
            ];
        }

        return $details;
    }
}
