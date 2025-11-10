<?php
declare(strict_types=1);

namespace ZxArt\Hardware;

/**
 * Groups for hardware items.
 */
enum HardwareGroup: string
{
    case COMPUTERS = 'computers';
    case STORAGE = 'storage';
    case DOS = 'dos';
    case SOUND = 'sound';
    case CONTROLS = 'controls';
    case EXPANSION = 'expansion';
}
