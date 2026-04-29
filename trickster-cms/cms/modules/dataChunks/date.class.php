<?php

class dateDataChunk extends DataChunk implements ElementStorageValueHolderInterface
{
    use ElementStorageValueDataChunkTrait;

    public function convertStorageToDisplay()
    {
        if ($this->storageValue > 0) {
            $this->displayValue = date('d.m.Y', $this->storageValue);
        } else {
            $this->displayValue = '';
        }
    }

    public function convertStorageToForm()
    {
        if ($this->storageValue > 0) {
            $this->formValue = date('d.m.Y', $this->storageValue);
        } else {
            $this->formValue = '';
        }
    }

    public function convertFormToStorage()
    {
        $this->setStorageValue(strtotime($this->formValue));
    }

    public function setExternalValue($value)
    {
        if (is_int($value)) {
            $this->storageValue = $value;
        } else {
            $this->storageValue = strtotime($value);
        }
        $this->formValue = null;
        $this->convertStorageToDisplay();
    }
}


