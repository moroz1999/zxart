<?php

/**
 * Class fileElement
 *
 * @property string $title
 * @property string $file
 * @property string $fileName
 * @property string $image
 * @property string $imageFileName
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

    public function getImageUrl($preset = 'adminImage', $mobile = false)
    {
        $full = false;
        if (stripos($preset, 'full')) {
            $full = true;
            $zoom = 3;
        } else {
            $zoom = 1;
        }
        $controller = $this->getService('controller');
        if (strtolower(pathinfo($this->fileName, PATHINFO_EXTENSION) == 'scr')) {
            $url = $controller->baseURL . 'zxscreen/type:standard/id:' . $this->file . '/zoom:' . $zoom . '/filename:image.png';
        } elseif (strtolower(pathinfo($this->fileName, PATHINFO_EXTENSION) == 'img')) {
            $url = $controller->baseURL . 'zxscreen/type:gigascreen/id:' . $this->file . '/zoom:' . $zoom . '/filename:image.png';
        } elseif (strtolower(pathinfo($this->fileName, PATHINFO_EXTENSION) == 'mlt')) {
            $url = $controller->baseURL . 'zxscreen/type:mlt/id:' . $this->file . '/zoom:' . $zoom . '/filename:image.png';
        } elseif (strtolower(pathinfo($this->fileName, PATHINFO_EXTENSION) == 'ifl')) {
            $url = $controller->baseURL . 'zxscreen/type:multicolor/id:' . $this->file . '/zoom:' . $zoom . '/filename:image.png';
        } elseif ($full) {
            $url = $controller->baseURL . '/release/id:' . $this->file . '/mode:view/filename:.' . $this->fileName;
        } else {
            $url = $controller->baseURL . 'image/type:' . $preset . '/id:' . $this->file . '/filename:' . $this->fileName;
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
                    ['png', 'jpg', 'jpeg', 'gif', 'bmp', 'tiff', 'scr', 'mlt', 'ifl', 'img']
                )) {
                    return true;
                }
            }
        }
        return false;
    }
}