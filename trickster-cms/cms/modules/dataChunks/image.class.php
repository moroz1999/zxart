<?php

use App\Paths\PathsManager;

class imageDataChunk extends DataChunk implements ElementStorageValueHolderInterface, ExtraDataHolderDataChunkInterface
{
    use ElementStorageValueDataChunkTrait;
    public $temporaryName;
    public $originalName;
    public $fileReceived = false;
    /**
     * @var mixed|string
     */
    private ?string $mime = null;
    private ?int $height = null;
    private ?int $width = null;

    public function setFormValue($value)
    {
        if (is_array($value)) {
            if ($value['tmp_name'] !== '' && file_exists($value['tmp_name'])) {
                $this->formValue = $value;
            }
            if ($value['tmp_name'] !== '') {
                $this->temporaryName = basename($value['tmp_name']);
            }
            if ($value['name'] !== '') {
                $this->originalName = $value['name'];
            }
        }
    }

    public function convertStorageToDisplay()
    {
        $this->displayValue = $this->storageValue;
    }

    public function convertStorageToForm()
    {
        $this->formValue = $this->storageValue;
    }

    public function convertFormToStorage()
    {
        if ($this->formValue && !$this->fileReceived) {
            $pathsManager = $this->getService(PathsManager::class);
            $cachePath = $pathsManager->getPath('uploadsCache');
            $pathsManager->ensureDirectory($cachePath);
            if (is_uploaded_file($this->formValue['tmp_name'])) {
                move_uploaded_file($this->formValue['tmp_name'], $cachePath . $this->temporaryName);
            } else {
                copy($this->formValue['tmp_name'], $cachePath . $this->temporaryName);
            }
            $this->fileReceived = true;
            $info = getimagesize($cachePath . $this->temporaryName);
            $this->width = $info['0'];
            $this->height = $info['1'];
            $this->mime = $info['mime'];
        }
    }

    public function persistExtraData()
    {
        if (!is_null($this->temporaryName)) {
            $pathsManager = $this->getService(PathsManager::class);
            $cachePath = $pathsManager->getPath('uploadsCache');
            $pathsManager->ensureDirectory($cachePath);
            $uploadsPath = $pathsManager->getPath('uploads');
            $pathsManager->ensureDirectory($uploadsPath);
            if (is_file($cachePath . $this->temporaryName)) {
                copy($cachePath . $this->temporaryName, $uploadsPath . $this->storageValue);
                unlink($cachePath . $this->temporaryName);
            }
        }
    }

    public function deleteExtraData()
    {
        $file = $this->getService(PathsManager::class)->getPath('uploads') . $this->displayValue;
        if (is_file($file)) {
            unlink($file);
        }
    }

    public function copyExtraData($oldValue, $oldId, $newId)
    {
        $copyPerformed = false;
        if ($oldValue) {
            $newValue = str_ireplace($oldId, $newId, $oldValue);
            $pathsManager = $this->getService(PathsManager::class);
            $uploadsPath = $pathsManager->getPath('uploads');
            $oldFile = $uploadsPath . $oldValue;
            $newFile = $uploadsPath . $newValue;
            if (file_exists($oldFile) && is_file($oldFile)) {
                copy($oldFile, $newFile);
            }
            $this->setStorageValue($newValue);
            $copyPerformed = true;
        }
        return $copyPerformed;
    }

    public function setExternalValue($value)
    {
        $this->storageValue = $value;
        $this->formValue = null;
        $this->convertStorageToDisplay();
    }
}



