<?php

class passwordDataChunk extends DataChunk implements ElementStorageValueHolderInterface
{
    use ElementStorageValueDataChunkTrait;

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
        if ($this->formValue != '') {
            $this->setStorageValue(password_hash($this->formValue, PASSWORD_DEFAULT));
        }
    }

    public function setExternalValue($value)
    {
        if ($value != '') {
            $this->storageValue = password_hash($value, PASSWORD_DEFAULT);
            $this->formValue = null;
            $this->convertStorageToDisplay();
        }
    }
}


