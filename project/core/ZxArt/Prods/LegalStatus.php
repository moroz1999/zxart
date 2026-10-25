<?php
declare(strict_types=1);

namespace ZxArt\Prods;

/**
 * The legal status of a production, stored and sent under its case name.
 */
enum LegalStatus: string
{
    case insales = 'insales';
    case forbidden = 'forbidden';
    case unknown = 'unknown';
    case mia = 'mia';
    case unreleased = 'unreleased';
    case recovered = 'recovered';
    case forbiddenzxart = 'forbiddenzxart';
    case allowedzxart = 'allowedzxart';
    case allowed = 'allowed';
    case donationware = 'donationware';
}
