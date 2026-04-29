<?php

trait GalleryInfoProviderTrait
{
    use ImageUrlGenerator;
    public function getGalleryJsonInfo($galleryOptions = [], $imagePresetBase = 'gallery')
    {
        $galleryData = [
            'id' => $this->id,
            'galleryResizeType' => 'viewport',
            'galleryWidth' => false,
            'galleryHeight' => false,
            'imageResizeType' => 'resize',
            'changeDelay' => 6000,
            'imageAspectRatio' => false,
            'thumbnailsSelectorEnabled' => true,
            'thumbnailsSelectorHeight' => '15%',
            'imagesButtonsEnabled' => false,
            'playbackButtonEnabled' => false,
            'imagesPrevNextButtonsEnabled' => false,
            'fullScreenGalleryEnabled' => true,
            'fullScreenButtonEnabled' => false,
            'descriptionType' => 'overlay',
            'descriptionEffect' => 'none',
            'imagesPrevNextButtonsSeparated' => false,
            'videoAutoStart' => true,

            'mobileGalleryHeight' => null,
            'mobileGalleryResizeType' => null,
            'mobileImageResizeType' => null,
            'mobileThumbnailsSelectorEnabled' => null,
            'mobileEnablePrevNextImagesButtons' => null,
            'mobileImagesButtonsEnabled' => null,
        ];
        $galleryData = array_merge($galleryData, $galleryOptions);

        $galleryData['images'] = [];
        $controller = controller::getInstance();
        foreach ($this->getImagesList() as $imageElement) {
            $imageInfo = [
                'title' => $imageElement->title,
                'description' => $imageElement->description,
                'alt' => $imageElement->alt,
                'link' => $imageElement->link,
                'externalLink' => $imageElement->externalLink,
                'id' => $imageElement->id,
                'filename' => $imageElement->originalName,
                'fileUrl' => $controller->baseURL . 'file/id:' . $imageElement->id . '/' . $imageElement->originalName,
                'bigImageSrcSet' => $this->generateImageSrcSet($imageElement->id, $imageElement->originalName, $imagePresetBase . 'Image'),
            ];
            if ($imageElement instanceof ImageUrlProviderInterface) {
                $imageInfo['fullImageUrl'] = $imageElement->getImageUrl($imagePresetBase . 'FullImage');
                $imageInfo['bigImageUrl'] = $imageElement->getImageUrl($imagePresetBase . 'Image');
                $imageInfo['thumbnailImageUrl'] = $imageElement->getImageUrl($imagePresetBase . 'SmallThumbnailImage');

                $imageInfo['mobileFullImageUrl'] = $imageElement->getImageUrl($imagePresetBase . 'FullImageMobile', true);
                $imageInfo['mobileBigImageUrl'] = $imageElement->getImageUrl($imagePresetBase . 'ImageMobile', true);
                $imageInfo['mobileThumbnailImageUrl'] = $imageElement->getImageUrl($imagePresetBase . 'SmallThumbnailImageMobile', true);
            } else {
                $imageId = $imageElement->image;
                $imageName = $imageElement->originalName;
                $imageInfo['fullImageUrl'] = $controller->baseURL . 'image/type:' . $imagePresetBase . 'FullImage/id:' . $imageId . '/filename:' . $imageName;
                $imageInfo['bigImageUrl'] = $controller->baseURL . 'image/type:' . $imagePresetBase . 'Image/id:' . $imageId . '/filename:' . $imageName;
                $imageInfo['thumbnailImageUrl'] = $controller->baseURL . 'image/type:' . $imagePresetBase . 'SmallThumbnailImage/id:' . $imageId . '/filename:' . $imageName;
            }

            $galleryData['images'][] = $imageInfo;
        }
        return json_encode($galleryData);
    }

    abstract public function getImagesList();
}