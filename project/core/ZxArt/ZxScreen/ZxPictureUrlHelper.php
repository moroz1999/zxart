<?php
declare(strict_types=1);

namespace ZxArt\ZxScreen;

class ZxPictureUrlHelper
{
    private const string KEY_VALUE_SEPARATOR = '=';
    private const string PARAMETER_SEPARATOR = ';';

    public static function getUrl(string $baseURL, ZxPictureParametersDto $params): string
    {
        $controllerName = $params->controller ?? 'zximages';
        return $baseURL . $controllerName . '/' . self::getFileName($params);
    }

    public static function getFileName(ZxPictureParametersDto $params): string
    {
        $parameters = [];
        $parameters[] = 'id' . self::KEY_VALUE_SEPARATOR . $params->id;

        if ($params->rotation) {
            $parameters[] = 'rotation' . self::KEY_VALUE_SEPARATOR . $params->rotation;
        }

        if ($params->border !== null) {
            $parameters[] = 'border' . self::KEY_VALUE_SEPARATOR . $params->border;
        }
        $type = $params->type ?? '';
        if (ZxPictureFlickeringHelper::isFlickering($type) && $params->mode) {
            $parameters[] = 'mode' . self::KEY_VALUE_SEPARATOR . $params->mode;
        }

        if ($params->palette) {
            $parameters[] = 'pal' . self::KEY_VALUE_SEPARATOR . $params->palette;
        }

        if ($params->hidden) {
            $parameters[] = 'type' . self::KEY_VALUE_SEPARATOR . 'hidden';
        } elseif ($params->type) {
            $parameters[] = 'type' . self::KEY_VALUE_SEPARATOR . $params->type;
        }

        if ($params->zoom) {
            $parameters[] = 'zoom' . self::KEY_VALUE_SEPARATOR . $params->zoom;
        }

        if ($params->fileName) {
            $parameters[] = $params->fileName;
        }

        return implode(self::PARAMETER_SEPARATOR, $parameters);
    }
}
