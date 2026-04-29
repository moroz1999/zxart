<?php

class arrayDataChunk extends DataChunk
{
    protected $storageValue; //storage value for persistantObjects.

    public function convertStorageToDisplay()
    {
        if (!is_array($this->storageValue)) {
            $this->displayValue = [];
        } else {
            $this->displayValue = $this->storageValue;
        }
    }

    public function convertStorageToForm()
    {
        $this->formValue = $this->storageValue;
    }

    public function convertFormToStorage()
    {
        $this->setStorageValue($this->formValue);
    }

    public function setExternalValue($value)
    {
        $this->storageValue = $value;
        $this->formValue = null;
        $this->convertStorageToDisplay();
    }
}


