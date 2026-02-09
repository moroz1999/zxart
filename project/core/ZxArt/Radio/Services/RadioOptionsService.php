<?php

declare(strict_types=1);

namespace ZxArt\Radio\Services;

use Cache;
use countryElement;
use structureManager;
use ZxArt\Tunes\Repositories\TunesRepository;

readonly class RadioOptionsService
{
    private const string CACHE_KEY = 'radio_options';
    private const int CACHE_TTL = 3600;

    public function __construct(
        private Cache $cache,
        private structureManager $structureManager,
        private TunesRepository $tunesRepository,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        $cached = $this->cache->get(self::CACHE_KEY);
        if (is_array($cached)) {
            return $cached;
        }

        $yearRange = $this->tunesRepository->getYearRange();
        $countries = $this->loadCountries();
        $formatGroups = $this->tunesRepository->getAvailableFormatGroups();
        $formats = $this->tunesRepository->getAvailableFormats();

        $result = [
            'yearRange' => $yearRange,
            'countries' => $countries,
            'formatGroups' => $formatGroups,
            'formats' => $formats,
            'partyOptions' => ['any', 'yes', 'no'],
        ];

        $this->cache->set(self::CACHE_KEY, $result, self::CACHE_TTL);

        return $result;
    }

    /**
     * @return array<int, array{id: int, title: string}>
     */
    private function loadCountries(): array
    {
        $countries = [];
        foreach ($this->tunesRepository->getAuthorCountryIds() as $countryId) {
            $element = $this->structureManager->getElementById($countryId);
            if ($element instanceof countryElement) {
                $countries[] = [
                    'id' => $countryId,
                    'title' => html_entity_decode((string)$element->getTitle(), ENT_QUOTES),
                ];
            }
        }

        usort(
            $countries,
            static fn(array $left, array $right): int => strcasecmp($left['title'], $right['title'])
        );

        return $countries;
    }
}
