<?php

use App\Paths\PathsManager;

class filesDataChunk extends DataChunk implements ElementStorageValueHolderInterface
{
    use ElementStorageValueDataChunkTrait;

    public $temporaryName;
    public $originalName;
    public $fileReceived = false;

    public function convertStorageToDisplay()
    {
        $this->displayValue = $this->storageValue;
    }

    public function convertStorageToForm()
    {
    }

    public function convertFormToStorage()
    {
        if (!is_null($this->formValue) && !$this->fileReceived) {
            $pathsManager = $this->getService(PathsManager::class);
            $cachePath = $pathsManager->getPath('uploadsCache');
            $pathsManager->ensureDirectory($cachePath);
            foreach ($this->formValue as &$fileInfo) {
                if (is_uploaded_file($fileInfo['tmp_name'])) {
                    move_uploaded_file($fileInfo['tmp_name'], $cachePath . basename($fileInfo['tmp_name']));
                }
            }
            $this->fileReceived = true;
        }
        $this->storageValue = $this->formValue;
    }

    public function setExternalValue($value)
    {
        $this->formValue = $value;
        $this->convertFormToStorage();
    }
}
