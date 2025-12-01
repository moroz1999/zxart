<?php
declare(strict_types=1);

namespace ZxArt\Import\Services;

final class VtrdosAuthorParser
{
    /**
     * Parse generic author/publisher/group text into normalized entries.
     *
     */
    private function parseEntriesFromText(string $text): array
    {
        if (stripos($text, 'by ') === 0) {
            $text = substr($text, 3);
        }

        $authorsPart = $text;
        if (str_contains($text, ' - ')) {
            [$authorsPart] = explode(' - ', $text, 2);
        }

        $authorsPart = trim(
            preg_replace('!\s+!', ' ', $authorsPart),
            " \t\n\r\0\x0B" . chr(0xC2) . chr(0xA0)
        );

        if ($authorsPart === '' || strcasecmp($authorsPart, 'n/a') === 0) {
            return [];
        }

        $rawParts = explode(',', $authorsPart);
        $entries = [];

        foreach ($rawParts as $part) {
            $name = trim($part);
            $year = null;

            if (preg_match("#'([0-9]+)#", $name, $matches)) {
                $digits = trim($matches[1] ?? '');
                if ($digits !== '') {
                    $digitsLength = strlen($digits);
                    if ($digitsLength === 2) {
                        $twoDigitYear = (int)$digits;
                        $year = $twoDigitYear > 50
                            ? (int)('19' . $digits)
                            : (int)('20' . $digits);
                    } elseif ($digitsLength === 4) {
                        $year = (int)$digits;
                    }
                }

                $name = trim(preg_replace("#('[0-9]+)#", '', $name));
            }

            if ($name === '') {
                continue;
            }

            $entries[] = [
                'name' => $name,
                'year' => $year,
            ];
        }

        return $entries;
    }

    /**
     * Add Label object if it does not exist yet in labelsOut.
     *
     * @param Label[] $labelsOut
     */
    private function addLabelIfMissing(string $name, array &$labelsOut, ?bool $isGroup = null): void
    {
        foreach ($labelsOut as $existingLabel) {
            if (($existingLabel->id ?? null) === $name) {
                return;
            }
        }

        $labelsOut[] = new Label(
            id: $name,
            name: $name,
            isAlias: null,
            isPerson: null,
            isGroup: $isGroup,
            countryId: null
        );
    }

    /**
     * Scenario 1: publishers + group.
     *
     * Rules:
     * - One entry:
     *      - this is publisher
     *      - its year is release year
     *      - prod year equals release year (no group)
     * - Two or more entries:
     *      - first is group
     *      - second is publisher
     *      - year near group is prod year
     *      - year near publisher is release year
     *      - if group year is missing but publisher year exists, prod year equals publisher year
     *      - remaining entries are treated as undetermined
     *
     * @param string[] $roles
     * @param Label[]                $labelsOut
     * @param string[] $groupsOut
     * @param string[] $publishersOut
     * @param array<string,string[]> $undeterminedOut
     */
    public function parseInfo(
        string  $text,
        array   $roles,
        ?int    &$prodYearOut,
        ?int    &$releaseYearOut,
        array &$labelsOut,
        array   &$groupsOut,
        array   &$publishersOut,
        array   &$undeterminedOut,
        ?string &$releaseTypeOut = null
    ): void
    {
        $prodYearOut = null;
        $releaseYearOut = null;
        $labelsOut = [];
        $groupsOut = [];
        $publishersOut = [];
        $undeterminedOut = [];
        $releaseTypeOut = null;

        $entries = $this->parseEntriesFromText($text);
        $entriesCount = count($entries);

        if ($entriesCount === 0) {
            return;
        }

        // Fill labels for all entries first
        foreach ($entries as $entry) {
            $this->addLabelIfMissing($entry['name'], $labelsOut);
        }

        if ($entriesCount === 1) {
            $publisher = $entries[0];
            $publishersOut[] = $publisher['name'];

            if ($publisher['year'] !== null) {
                $releaseYearOut = $publisher['year'];
                $prodYearOut = $publisher['year'];
            }

            return;
        }

        // entriesCount >= 2
        $group = $entries[0];
        $publisher = $entries[1];

        $groupsOut[] = $group['name'];
        $publishersOut[] = $publisher['name'];

        if ($publisher['year'] !== null) {
            $releaseYearOut = $publisher['year'];
        }

        if ($group['year'] !== null) {
            $prodYearOut = $group['year'];
        } elseif ($publisher['year'] !== null) {
            $prodYearOut = $publisher['year'];
        }

        $extraEntries = array_slice($entries, 2);
        foreach ($extraEntries as $entry) {
            $name = $entry['name'];
            if ($name === '') {
                continue;
            }
            $undeterminedOut[$name] = $roles;
        }
    }

    /**
     * Scenario 2: everything goes to undetermined.
     *
     * Rules:
     * - If trimmed text is exactly "author" (case-insensitive), releaseType becomes "original"
     *   and nothing else is parsed.
     * - Otherwise:
     *      - Every string is undetermined
     *      - Any year here is treated as release year (first one wins)
     *
     * @param string[] $roles
     * @param Label[]                $labelsOut
     * @param array<string,string[]> $undeterminedOut
     */
    public function parseVersion(
        string  $text,
        array   $roles,
        ?int    &$releaseYearOut,
        array &$labelsOut,
        array   &$undeterminedOut,
        ?string &$releaseTypeOut = null
    ): void
    {
        $releaseYearOut = null;
        $labelsOut = [];
        $undeterminedOut = [];
        $releaseTypeOut = null;

        $trimmedText = trim($text);
        if (strcasecmp($trimmedText, 'author') === 0) {
            $releaseTypeOut = 'original';
            return;
        }

        $entries = $this->parseEntriesFromText($text);

        foreach ($entries as $entry) {
            $name = $entry['name'];
            if ($name === '') {
                continue;
            }

            $this->addLabelIfMissing($name, $labelsOut);

            if ($entry['year'] !== null && $releaseYearOut === null) {
                $releaseYearOut = $entry['year'];
            }

            $undeterminedOut[$name] = $roles;
        }
    }
}
