<?php

class jsonSerializedDataChunk extends DataChunk implements ElementStorageValueHolderInterface
{
    use ElementStorageValueDataChunkTrait;

    public function convertStorageToDisplay()
    {
        if ($this->storageValue != '') {
            $this->displayValue = (array)json_decode($this->storageValue, true);
        } else {
            $this->displayValue = [];
        }
    }

    public function convertStorageToForm()
    {
        if ($this->storageValue) {
            $this->formValue = (array)json_decode($this->storageValue, true);
        }
    }

    public function convertFormToStorage()
    {
        $storageValue = "";
        if ($this->formValue) {
            $storageValue = json_encode($this->formValue);
        }
        $this->setStorageValue($storageValue);
    }

    public function setExternalValue($value)
    {
        $this->storageValue = $value;
        $this->formValue = null;
        $this->convertStorageToDisplay();
    }

    public function setFormValue($value)
    {
        if (!is_array($value)) {
            $this->formValue = json_decode($value, true);
        } else {
            $this->formValue = $value;
        }
    }
}

