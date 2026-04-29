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
class fileElement extends structureElement implements StructureElementUploadedFilesPathInterface, ImageUrlProviderInterface
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

    public function getImageId()
    {
        if ($this->image) {
            return $this->image;
        }
        return $this->file;
    }

    public function getImageName()
    {
        if ($this->imageFileName) {
            return $this->imageFileName;
        }
        return $this->fileName;
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
        $controller = $this->getService(controller::class);
        $url = $controller->baseURL . $appName . '/id:' . $this->file . '/mode:' . $mode . '/filename:' . $this->fileName;

        return $url;
    }

    public function isImage()
    {
        if ($extension = $this->getFileExtension()) {
            if (in_array(strtolower($extension), ['png', 'jpg', 'jpeg', 'gif', 'bmp', 'tiff'])) {
                return true;
            }
        }
        return false;
    }
}