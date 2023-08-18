<?php

/**
 * Class fileElement
 *
 * @property string $title
 * @property string $file
 * @property string $fileName
 * @property string $image
 * @property string $imageFileName
 * @property string $author
 */
class fileElement extends structureElement implements StructureElementUploadedFilesPathInterface,
    ImageUrlProviderInterface
{
    use ImageUrlProviderTrait;

    public $dataResourceName = 'module_file';
    public $defaultActionName = 'show';
    public $role = 'content';

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['file'] = 'file';
        $moduleStructure['fileName'] = 'fileName';
        $moduleStructure['image'] = 'image';
        $moduleStructure['imageFileName'] = 'fileName';
        $moduleStructure['author'] = 'text';
    }

    public function getUploadedFilesPath()
    {
        if ($parentElement = $this->getFirstParentElement()) {
            if ($parentElement instanceof StructureElementUploadedFilesPathInterface) {
                return $parentElement->getUploadedFilesPath();
            }
        }
        return false;
    }

    public function getImageId($mobile = false)
    {
        if ($this->image) {
            return $this->image;
        }
        return $this->file;
    }

    public function getImageName($mobile = false)
    {
        if ($this->imageFileName) {
            return $this->imageFileName;
        }
        return $this->fileName;
    }

    public function getImageUrl($preset = 'original', $mobile = false, $full = false, $zoom = 1)
    {
        if (stripos($preset, 'full') !== false) {
            $full = true;
            $zoom = 2;
        }
        $controller = $this->getService('controller');
        if (strtolower(pathinfo($this->fileName, PATHINFO_EXTENSION) == 'scr')) {
            $url = $controller->baseURL . 'zxscreen/type:standard/id:' . $this->file . '/zoom:' . $zoom . '/filename:image.png';
        } elseif (strtolower(pathinfo($this->fileName, PATHINFO_EXTENSION) == 'img')) {
            $url = $controller->baseURL . 'zxscreen/type:gigascreen/id:' . $this->file . '/zoom:' . $zoom . '/filename:image.png';
        } elseif (strtolower(pathinfo($this->fileName, PATHINFO_EXTENSION) == 'nxi')) {
            $url = $controller->baseURL . 'zxscreen/type:nxi/id:' . $this->file . '/zoom:' . $zoom . '/filename:image.png';
        } elseif (strtolower(pathinfo($this->fileName, PATHINFO_EXTENSION) == 'sl2')) {
            $url = $controller->baseURL . 'zxscreen/type:sl2/id:' . $this->file . '/zoom:' . $zoom . '/filename:image.png';
        } elseif (strtolower(pathinfo($this->fileName, PATHINFO_EXTENSION) == 'ssx')) {
            $url = $controller->baseURL . 'zxscreen/type:ssx/id:' . $this->file . '/zoom:' . $zoom . '/filename:image.png';
        } elseif (strtolower(pathinfo($this->fileName, PATHINFO_EXTENSION) == 'mlt')) {
            $url = $controller->baseURL . 'zxscreen/type:mlt/id:' . $this->file . '/zoom:' . $zoom . '/filename:image.png';
        } elseif (strtolower(pathinfo($this->fileName, PATHINFO_EXTENSION) == 'ifl')) {
            $url = $controller->baseURL . 'zxscreen/type:multicolor/id:' . $this->file . '/zoom:' . $zoom . '/filename:image.png';
        } elseif ($full) {
            $url = $controller->baseURL . 'release/id:' . $this->file . '/mode:view/filename:' . $this->fileName;
        } else {
            $filename = pathinfo($this->fileName, PATHINFO_FILENAME);
            $url = $controller->baseURL . 'image/type:' . $preset . '/id:' . $this->file . '/filename:' . $filename . '.webp';
        }
        return $url;
    }

    public function getFileName($encoded = false)
    {
        if ($encoded) {
            return $this->fileName;
        } else {
            return urldecode($this->fileName);
        }
    }

    public function getFileExtension()
    {
        if ($info = pathinfo($this->fileName)) {
            if (!empty($info['extension'])) {
                return $info['extension'];
            }
        }

        return false;
    }

    public function getDownloadUrl($mode = 'download', $appName = 'file')
    {
        $controller = $this->getService('controller');
        $url = $controller->baseURL . $appName . '/id:' . $this->file . '/mode:' . $mode . '/filename:' . $this->fileName;

        return $url;
    }

    public function isImage()
    {
        if ($info = pathinfo($this->fileName)) {
            if (!empty($info['extension'])) {
                if (in_array(
                    strtolower($info['extension']),
                    ['png', 'jpg', 'jpeg', 'gif', 'bmp', 'tiff', 'scr', 'mlt', 'ifl', 'img', 'ssx']
                )) {
                    return true;
                }
            }
        }
        return false;
    }
}