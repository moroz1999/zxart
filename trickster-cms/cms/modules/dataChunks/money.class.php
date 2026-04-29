<?php

class moneyDataChunk extends DataChunk implements ElementStorageValueHolderInterface
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
        if ($value < 0) {
            $value = $value * (-1);
        }
        $this->setStorageValue($value);
    }

    public function setExternalValue($value)
    {
        $value = floatval(str_replace(',', '.', $value));
        if ($value < 0) {
            $value = $value * (-1);
        }
        $this->storageValue = $value;
        $this->formValue = null;
        $this->convertStorageToDisplay();
    }
}

