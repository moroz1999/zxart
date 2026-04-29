<?php

class floatNumberDataChunk extends DataChunk implements ElementStorageValueHolderInterface
{
    use ElementStorageValueDataChunkTrait;

    public function convertStorageToDisplay()
    {
        $this->displayValue = sprintf("%.2f", $this->storageValue);
    }

    public function convertStorageToForm()
    {
        $this->formValue = floatval($this->storageValue);
    }

    public function convertFormToStorage()
    {
        $value = str_replace([" ", ','], ["", '.'], $this->formValue);
        $value = floatval($value);
        $this->setStorageValue($value);
    }

    public function setExternalValue($value)
    {
        $this->storageValue = floatval(str_replace(',', '.', $value));
        $this->formValue = null;
        $this->convertStorageToDisplay();
    }
}


