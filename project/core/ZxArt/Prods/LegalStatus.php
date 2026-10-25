<?php
declare(strict_types=1);

namespace ZxArt\Prods;

enum LegalStatus
{
    case insales;
    case forbidden;
    case unknown;
    case mia;
    case unreleased;
    case recovered;
    case forbiddenzxart;
    case allowedzxart;
    case allowed;
    case donationware;

    /**
     * The status a production stores under its case name.
     */
    public static function tryFromName(string $name): ?self
    {
        foreach (self::cases() as $status) {
            if ($status->name === $name) {
                return $status;
            }
        }

        return null;
    }
}
