<?php

use App\ZxScreen\Helper;
use App\ZxScreen\ParametersDto;

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

    /**
     * @return void
     */
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

    public function getFilePath(): string
    {
        return $this->getUploadedFilesPath() . $this->getId();
    }

    /**
     * @return string
     */
    public function getImageId($mobile = false)
    {
        if ($this->image) {
            return $this->image;
        }
        return $this->file;
    }

    /**
     * @return string
     */
    public function getImageName($mobile = false)
    {
        if ($this->imageFileName) {
            return $this->imageFileName;
        }
        return $this->fileName;
    }

    public function getImageUrl(string $preset = 'original', $mobile = false, $full = false, $zoom = 1): ?string
    {
        $controller = $this->getService('controller');
        $extension = strtolower(pathinfo($this->fileName, PATHINFO_EXTENSION));

        if (stripos($preset, 'full') !== false) {
            $full = true;
            $zoom = 2;
        }


        $type = match ($extension) {
            'scr' => 'standard',
            'img' => 'gigascreen',
            'nxi' => 'nxi',
            'sl2' => 'sl2',
            'ssx' => 'ssx',
            'mlt' => 'mlt',
            'ifl' => 'multicolor',
            default => null
        };

        if (!$type) {
            if ($full) {
                return $controller->baseURL . 'screenshot/id:' . $this->file . '/filename:' . $this->fileName;
            }
            $filename = pathinfo($this->fileName, PATHINFO_FILENAME);
            return $controller->baseURL . 'image/type:' . $preset . '/id:' . $this->file . '/filename:' . $filename . '.webp';
        }

        $fileName = 'image.png';

        $params = new ParametersDto(
            baseURL: $controller->baseURL,
            type: $type,
            zoom: $zoom,
            id: $this->file,
            fileName: $fileName
        );

        return Helper::getUrl($params);
    }

    public function getFileName($encoded = false): string
    {
        if ($encoded) {
            return $this->fileName;
        } else {
            return urldecode($this->fileName);
        }
    }

    /**
     * @return false|string
     */
    public function getFileExtension(): string|false
    {
        if ($info = pathinfo($this->fileName)) {
            if (!empty($info['extension'])) {
                return strtolower($info['extension']);
            }
        }

        return false;
    }

    public function getDownloadUrl(string $mode = 'download', string $appName = 'file'): string
    {
        $controller = $this->getService('controller');
        $url = $controller->baseURL . $appName . '/id:' . $this->file . '/mode:' . $mode . '/filename:' . $this->fileName;

        return $url;
    }

    public function getScreenshotUrl(): string
    {
        $controller = $this->getService('controller');
        $url = $controller->baseURL . 'screenshot' . '/id:' . $this->file . '/filename:' . $this->fileName;

        return $url;
    }

    public function isImage(): bool
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