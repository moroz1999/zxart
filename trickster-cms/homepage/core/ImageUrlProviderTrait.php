<?php

trait ImageUrlProviderTrait
{
    public function getImageId()
    {
        return $this->image;
    }

    public function getImageName()
    {
        return $this->originalName;
    }

    public function getImageUrl(string $preset = 'adminImage'): ?string
    {
        $controller = $this->getService(controller::class);
        return $controller->baseURL . 'image/type:' . $preset . '/id:' . $this->getImageId() . '/filename:' . $this->getImageName();
    }
}