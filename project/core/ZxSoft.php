<?php

trait ZxSoft
{
    static private $extendedGraphics = ['zxevolution', 'zxnext', 'elementzxmb', 'zxuno', 'baseconf', 'tsconf', 'gmx'];

    public function getListImagePreset()
    {
        foreach ($this->getHardwareInfo() as $item) {
            if (in_array($item['id'], self::$extendedGraphics)) {
                return 'full';
            }
        }
        return 'prodListImage';
    }
}