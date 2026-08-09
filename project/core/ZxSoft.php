<?php

trait ZxSoft
{
    static private array $extendedGraphics = ['zxevolution', 'zxnext', 'elementzxmb', 'zxuno', 'baseconf', 'tsconf', 'gmx'];

    /**
     * Extended-graphics machines are computer codes, and after hardware moved to
     * the production a release no longer repeats them — so this asks for the set
     * that describes what the item actually runs on, not for its own codes.
     *
     * @psalm-return 'full'|'prodListImage'
     */
    public function getListImagePreset(): string
    {
        foreach ($this->getRunsOnHardwareCodes() as $code) {
            if (in_array($code, self::$extendedGraphics, true)) {
                return 'full';
            }
        }
        return 'prodListImage';
    }
}
