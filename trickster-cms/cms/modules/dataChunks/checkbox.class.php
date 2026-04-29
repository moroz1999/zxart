<?php

class checkboxDataChunk extends DataChunk implements ElementStorageValueHolderInterface
{
    use ElementStorageValueDataChunkTrait;

    public function setFormValue($value)
    {
        if ($value != 1) {
            $value = 0;
        }
        $this->formValue = $value;
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
        if (!is_null($this->formValue) && $this->formValue != 0) {
            $this->setStorageValue(1);
        } else {
            $this->setStorageValue(0);
        }
    }

    public function setExternalValue($value)
    {
        $this->storageValue = $value;
        $this->formValue = null;
        $this->convertStorageToDisplay();
    }
}


