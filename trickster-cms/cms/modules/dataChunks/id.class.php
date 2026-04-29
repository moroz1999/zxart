<?php

class idDataChunk extends DataChunk implements ElementStorageValueHolderInterface
{
    use ElementStorageValueDataChunkTrait;

    public function convertStorageToDisplay()
    {
        $this->displayValue = (int)$this->storageValue;
    }

    public function convertStorageToForm()
    {
        $this->formValue = (int)$this->storageValue;
    }

    public function convertFormToStorage()
    {
        $this->setStorageValue((int)$this->formValue);
    }

    public function setExternalValue($value)
    {
        $this->storageValue = (int)$value;
        $this->formValue = null;
        $this->convertStorageToDisplay();
    }
}


