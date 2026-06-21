<?php
declare(strict_types=1);

use ZxArt\Hardware\HardwareCatalog;

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
}
