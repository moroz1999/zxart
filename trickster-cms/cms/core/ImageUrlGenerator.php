<?php

trait ImageUrlGenerator
{
    protected static $srcSetPresets = ['1.25', '1.5', '1.75', '2', '3'];

    public function generateImageUrl($imageId, $fileName, $type, $multiplier = false)
    {
        $result = controller::getInstance()->baseURL . 'image/type:' . $type
            . '/id:' . $imageId;
        if ($multiplier) {
            $result .= '/multiplier:' . $multiplier;
        }
        $result .= '/' . $fileName;
        return $result;
    }

    public function generateImageSrcSet($imageId, $fileName, $type)
    {
        $urls = [];
        foreach (self::$srcSetPresets as $preset) {
            $urls[] = $this->generateImageUrl($imageId, $fileName, $type, $preset) . ' ' . $preset . 'x';
        }
        return implode(',', $urls);
    }
}
