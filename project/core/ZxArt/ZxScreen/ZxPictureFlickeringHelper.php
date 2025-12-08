<?php
declare(strict_types=1);


namespace ZxArt\ZxScreen;

final class ZxPictureFlickeringHelper
{
    public static function isFlickering(string $type): bool {
        return in_array(
            $type,
            [
                'gigascreen',
                'tricolor',
                'mg1',
                'mg2',
                'mg4',
                'mg8',
                'lowresgs',
                'stellar',
                'chr$',
                'bsp',
            ]
        );

    }
}