<?php

class emptyMoneyDataChunk extends DataChunk implements ElementStorageValueHolderInterface
{
    use ElementStorageValueDataChunkTrait;

    public function convertStorageToDisplay()
    {
        if ($this->storageValue === '') {
            $this->displayValue = '';
        } else {
            $this->displayValue = sprintf("%.2f", $this->storageValue);
        }
    }

    public function convertStorageToForm()
    {
        if ($this->storageValue === '') {
            $this->formValue = '';
        } else {
            $this->formValue = floatval($this->storageValue);
        }
    }

    public function convertFormToStorage()
    {
        if ($this->formValue === '') {
            $value = '';
        } else {
            $value = str_replace([" ", ','], ["", '.'], $this->formValue);
            $value = floatval($value);
            if ($value < 0) {
                $value = $value * (-1);
            }
        }
        $this->setStorageValue($value);
    }

    public function setExternalValue($value)
    {
        if ($value !== '') {
            $value = floatval(str_replace(',', '.', $value));
            if ($value < 0) {
                $value = $value * (-1);
            }
        }
        $this->storageValue = $value;
        $this->formValue = null;
        $this->convertStorageToDisplay();
    }
}

