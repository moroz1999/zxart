<?php
declare(strict_types=1);

namespace ZxArt\Releases\Services;
final class ReleaseFormatsProvider
{
    /**
     * Centralized definition of all release formats grouped by category
     */
    private const array FORMATS = [
        'disk' => [
            'dsk',
            'trd',
            'scl',
            'fdi',
            'udi',
            'td0',
            'mgt',
            'opd',
            'mld',
            'mbd',
            'img',
            'sad',
            'd40',
            'd80',
            'cpm',
        ],
        'tape' => [
            'tzx',
            'tap',
            'mdr',
            'p',
            'o',
        ],
        'rom' => [
            'bin',
            'rom',
            'spg',
            'nex',
            'snx',
            'tar',
        ],
        'snapshot' => [
            'sna',
            'szx',
            'dck',
            'z80',
            'z81',
            'slt',
            '$b',
            '$c',
        ],
    ];

    /**
     * Icon CSS classes for each format group.
     * Adjust to your icon set (e.g., Font Awesome aliases or custom sprite classes).
     */
    private const array GROUP_ICON_EMOJI = [
        'disk' => '💾',
        'tape' => '📼',
        'rom' => '💻',
        'snapshot' => '🔢',
        'unknown' => '📄',
    ];

    /**
     * Returns all release formats as a flat list
     */
    public function getReleaseFormats(): array
    {
        return array_merge(...array_values(self::FORMATS));
    }

    /**
     * Returns release formats grouped by type
     */
    public function getGroupedReleaseFormats(): array
    {
        return self::FORMATS;
    }

    /**
     * Returns a map format => group for fast lookup.
     * @return array<string, string>
     */
    private function getFormatToGroupMap(): array
    {
        $map = [];
        foreach (self::FORMATS as $group => $formats) {
            foreach ($formats as $format) {
                $map[$format] = $group;
            }
        }
        return $map;
    }

    /**
     * Returns group name for a given format or 'unknown' if not found.
     */
    private function getFormatGroup(string $format): string
    {
        $map = $this->getFormatToGroupMap();
        return $map[$format] ?? 'unknown';
    }

    public function getFormatEmoji(string $format): string
    {
        $group = $this->getFormatGroup($format);
        return self::GROUP_ICON_EMOJI[$group] ?? self::GROUP_ICON_EMOJI['unknown'];
    }
}
