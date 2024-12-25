<?php

trait ZxSoft
{
    static private array $extendedGraphics = ['zxevolution', 'zxnext', 'elementzxmb', 'zxuno', 'baseconf', 'tsconf', 'gmx'];

    /**
     * @psalm-return 'full'|'prodListImage'
     */
    public function getListImagePreset(): string
    {
        $hardwareInfo = $this->getHardwareInfo();
        if (!empty($hardwareInfo)) {
            foreach ($this->getHardwareInfo() as $item) {
                if (in_array($item['id'], self::$extendedGraphics, true)) {
                    return 'full';
                }
            }
        }
        return 'prodListImage';
    }
}