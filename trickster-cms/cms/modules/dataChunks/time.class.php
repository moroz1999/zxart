<?php

class timeDataChunk extends DataChunk implements ElementStorageValueHolderInterface
{
    use ElementStorageValueDataChunkTrait;

    public function convertStorageToDisplay()
    {
        if ($this->storageValue > 0) {
            $hours = floor($this->storageValue / 3600);
            $minutes = floor(($this->storageValue - 3600 * $hours) / 60);

            $this->displayValue = sprintf('%02.0f', $hours) . ':' . sprintf('%02.0f', $minutes);
        } else {
            $this->displayValue = '';
        }
    }

    public function convertStorageToForm()
    {
        if ($this->storageValue > 0) {
            $hours = floor($this->storageValue / 3600);
            $minutes = floor(($this->storageValue - 3600 * $hours) / 60);

            $this->formValue = sprintf('%02.0f', $hours) . ':' . sprintf('%02.0f', $minutes);
        } else {
            $this->formValue = '';
        }
    }

    public function convertFormToStorage()
    {
        if ($value = $this->formValue) {
            $data = explode(':', $this->formValue);
            $value = $data[0] * 3600 + $data[1] * 60;
        }
        $this->setStorageValue($value);
    }

    public function setExternalValue($value)
    {
        $this->formValue = null;
        $data = explode(':', $value);
        $value = $data[0] * 3600 + $data[1] * 60;
        $this->setStorageValue($value);
    }
}

