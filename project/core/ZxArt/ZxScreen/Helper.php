<?php

namespace ZxArt\ZxScreen;

class Helper
{
    public static function getUrl(ParametersDto $params): string
    {
        $url = $params->baseURL . 'zxscreen/';

        if ($params->rotation) {
            $url .= 'rotation:' . $params->rotation . '/';
        }
        if ($params->border !== null) {
            $url .= 'border:' . $params->border . '/';
        }
        if ($params->download) {
            $url .= 'download:1/';
        }
        if ($params->mode) {
            $url .= 'mode:' . $params->mode . '/';
        }
        if ($params->palette) {
            $url .= 'pal:' . $params->palette . '/';
        }
        if ($params->hidden) {
            $url .= 'type:hidden/';
        } elseif ($params->type) {
            $url .= 'type:' . $params->type . '/';
        }
        if ($params->zoom) {
            $url .= 'zoom:' . $params->zoom . '/';
        }
        $url .= 'id:' . $params->id . '/';

        if ($params->fileName) {
            $url .= $params->fileName;
        }

        return $url;
    }
}