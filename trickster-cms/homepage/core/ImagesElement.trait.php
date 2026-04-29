<?php

/**
 * Class ImagesElementTrait
 *
 * @property array imagesSelector
 */
trait ImagesElementTrait
{
    protected $imagesList;
    //todo: restore this after PHP7.3 fix on Zone
//    use CacheOperatingElement;

    /**
     * @return galleryImageElement[]
     */
    public function getImagesList()
    {
        $structureManager = $this->getService('structureManager');
        if ($this->imagesList === null) {
            $cache = $this->getElementsListCache('imgs', 3600);
            if (($this->imagesList = $cache->load()) === null) {
                $this->imagesList = [];
                if ($childElements = $structureManager->getElementsChildren($this->id, null, $this->getImagesLinkType())) {
                    foreach ($childElements as $childElement) {
                        if ($childElement->structureType == 'galleryImage') {
                            $this->imagesList[] = $childElement;
                        }
                    }
                }
                $cache->save($this->imagesList);
            }
        }
        return $this->imagesList;
    }

    public function getImagesLinkType()
    {
        return 'connectedImage';
    }

    public function getImage($number = 0)
    {
        if ($images = $this->getImagesList()) {
            if (isset ($images[$number])) {
                return $images[$number];
            }
        }
        return false;
    }
}