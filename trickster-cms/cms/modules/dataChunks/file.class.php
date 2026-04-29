<?php

use App\Paths\PathsManager;

class fileDataChunk extends DataChunk implements ElementHolderInterface, ElementStorageValueHolderInterface, ExtraDataHolderDataChunkInterface
{
    use ElementStorageValueDataChunkTrait;
    public $temporaryName;
    public $originalName;
    public $fileReceived = false;
    use ElementHolderDataChunkTrait;

    public function __construct($fieldName)
    {
        parent::__construct($fieldName);
    }

    public function setFormValue($value)
    {
        if (is_array($value)) {
            if ($value['tmp_name'] != '' && file_exists($value['tmp_name'])) {
                $this->formValue = $value;
            }
            if ($value['tmp_name'] != '') {
                $this->temporaryName = basename($value['tmp_name']);
            }
            if ($value['name'] != '') {
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
        if (!is_null($this->formValue) && !$this->fileReceived) {
            $pathsManager = $this->getService(PathsManager::class);
            $cachePath = $pathsManager->getPath('uploadsCache');
            $pathsManager->ensureDirectory($cachePath);
            if (isset($this->formValue['tmp_name'])) {
                if (is_uploaded_file($this->formValue['tmp_name'])) {
                    move_uploaded_file($this->formValue['tmp_name'], $cachePath . $this->temporaryName);
                } else {
                    copy($this->formValue['tmp_name'], $cachePath . $this->temporaryName);
                }
            }
            $this->fileReceived = true;
        }
        if (!is_array($this->formValue) && $this->formValue) {
            $this->setStorageValue(htmlspecialchars($this->formValue, ENT_QUOTES));
        }
    }

    public function persistExtraData()
    {
        if (!is_null($this->temporaryName)) {
            $pathsManager = $this->getService(PathsManager::class);
            $cachePath = $pathsManager->getPath('uploadsCache');
            $uploadsPath = $this->getUploadedFilesPath();
            $pathsManager->ensureDirectory($uploadsPath);
            $pathsManager->ensureDirectory($cachePath);
            if (is_file($cachePath . $this->temporaryName)) {
                copy($cachePath . $this->temporaryName, $uploadsPath . $this->storageValue);
                unlink($cachePath . $this->temporaryName);
            }
        }
    }

    public function deleteExtraData()
    {
        $uploadsFilePath = $this->getUploadedFilesPath();
        $file = $uploadsFilePath . $this->displayValue;
        if (file_exists($file) && is_file($file)) {
            unlink($file);
        }
    }

    public function copyExtraData($oldValue, $oldId, $newId)
    {
        $copyPerformed = false;
        if ($oldValue) {
            $uploadedFilesPath = $this->getUploadedFilesPath();

            $newValue = str_ireplace($oldId, $newId, $oldValue);

            $oldFile = $uploadedFilesPath . $oldValue;
            $newFile = $uploadedFilesPath . $newValue;
            if (file_exists($oldFile) && is_file($oldFile)) {
                copy($oldFile, $newFile);
            }
            $this->setStorageValue($newValue);
            $copyPerformed = true;
        }
        return $copyPerformed;
    }

    public function getUploadedFilePath()
    {
        $result = false;
        $pathsManager = $this->getService(PathsManager::class);
        $cachePath = $pathsManager->getPath('uploadsCache');
        $pathsManager->ensureDirectory($cachePath);
        if (is_file($cachePath . $this->temporaryName)) {
            $result = $cachePath . $this->temporaryName;
        }
        return $result;
    }

    public function getUploadedContents()
    {
        $result = false;
        if ($path = $this->getUploadedFilePath()) {
            $result = file_get_contents($path);
        }
        return $result;
    }

    public function getPersistedContents()
    {
        $result = false;

        return $result;
    }

    public function setExternalValue($value)
    {
        $this->storageValue = $value;
        $this->formValue = null;
        $this->convertStorageToDisplay();
    }

    public function getTemporaryFilePath()
    {
        $result = false;
        if ($this->fileReceived) {
            $pathsManager = $this->getService(PathsManager::class);
            $result = $pathsManager->getPath('uploadsCache') . $this->temporaryName;
        }
        return $result;
    }

    protected function getUploadedFilesPath()
    {
        if ($this->structureElement instanceof StructureElementUploadedFilesPathInterface) {
            return $this->structureElement->getUploadedFilesPath();
        }
        $pathsManager = $this->getService(PathsManager::class);
        return $pathsManager->getPath('uploads');
    }
}

