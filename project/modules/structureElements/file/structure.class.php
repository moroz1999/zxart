<?php

use ZxArt\ZxScreen\ZxPictureParametersDto;
use ZxArt\ZxScreen\ZxPictureUrlHelper;

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
class fileElement extends structureElement implements StructureElementUploadedFilesPathInterface, ImageUrlProviderInterface
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
        return $this->getUploadedFilesPath() . $this->getPersistedId();
    }

    /**
     * @return string
     */
    public function getImageId()
    {
        if ($this->image) {
            return $this->image;
        }
        return $this->file;
    }

    /**
     * @return string
     */
    public function getImageName()
    {
        if ($this->imageFileName) {
            return $this->imageFileName;
        }
        return $this->fileName;
    }

    public function getZxImageUrl(bool $full = false, int $zoom = 1)
    {
        return $this->generateImageUrl($full, $zoom, 'original');
    }

    public function getImageUrl(string $preset = 'original'): ?string
    {
        $full = stripos($preset, 'full') !== false;
        $zoom = $full ? 3 : 1;
        return $this->generateImageUrl($full, $zoom, $preset);
    }

    private function generateImageUrl(bool $full, int $zoom, string $preset): ?string
    {
        $baseUrl = $this->getService('controller')->baseURL;
        $extension = strtolower(pathinfo($this->fileName, PATHINFO_EXTENSION));
        $type = $this->resolveFileType($extension);

        if ($type === null) {
            return $this->buildFallbackUrl($baseUrl, $full, $preset);
        }

        $params = new ZxPictureParametersDto(
            type: $type,
            zoom: $zoom,
            id: (int)$this->file,
            mode: 'mix',
            palette: 'srgb'
        );

        return ZxPictureUrlHelper::getUrl($baseUrl, $params);
    }

    private function resolveFileType(string $extension): ?string
    {
        return match ($extension) {
            'scr' => 'standard',
            's80' => 's80',
            's81' => 's81',
            'img' => 'gigascreen',
            'nxi' => 'nxi',
            'sl2' => 'sl2',
            'ssx' => 'ssx',
            'mlt' => 'mlt',
            'ifl' => 'multicolor',
            default => null
        };
    }

    private function buildFallbackUrl(string $baseUrl, bool $full, string $preset): string
    {
        $extension = $this->getFileExtension();

        if ($full || $extension === 'gif') {
            return $baseUrl . 'screenshot/id:' . $this->file . '/' . $this->fileName;
        }
        $filename = pathinfo($this->fileName, PATHINFO_FILENAME);
        return $baseUrl . 'image/type:' . $preset . '/id:' . $this->file . '/filename:' . $filename . '.webp';
    }

    public function getFileName($encoded = false): string
    {
        if ($encoded) {
            return $this->fileName;
        }

        return urldecode($this->fileName);
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
        return $controller->baseURL . $appName . '/id:' . $this->file . '/mode:' . $mode . '/filename:' . $this->fileName;
    }

    public function getScreenshotUrl(): string
    {
        $controller = $this->getService('controller');
        return $controller->baseURL . 'screenshot' . '/id:' . $this->file . '/' . $this->fileName;
    }

    public function isImage(): bool
    {
        if ($info = pathinfo($this->fileName)) {
            if (!empty($info['extension'])) {
                if (in_array(
                    strtolower($info['extension']),
                    ['png', 'jpg', 'jpeg', 'gif', 'bmp', 'tiff', 'scr', 'mlt', 'ifl', 'img', 'ssx', 's80', 's81']
                )) {
                    return true;
                }
            }
        }
        return false;
    }
}