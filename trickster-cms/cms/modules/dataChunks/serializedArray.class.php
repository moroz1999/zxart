<?php

class serializedArrayDataChunk extends DataChunk implements ElementStorageValueHolderInterface
{
    use ElementStorageValueDataChunkTrait;

    public function convertStorageToForm()
    {
        if ($this->storageValue) {
            $this->formValue = unserialize($this->storageValue);
            if (!$this->formValue) {
                $this->formValue = [$this->storageValue];
            }
        }
    }

    public function convertFormToStorage()
    {
        if (is_array($this->formValue)) {
            $list = (array)$this->formValue;
        } else {
            $list = [];
        }
        $storageValue = serialize($list);
        $this->setStorageValue($storageValue);
    }

    public function setExternalValue($value)
    {
        $this->storageValue = $value;
        $this->formValue = null;
        $this->convertStorageToDisplay();
    }

    public function convertStorageToDisplay()
    {
        if ($this->storageValue != '') {
            $this->displayValue = unserialize($this->storageValue);
            if (!$this->displayValue) {
                $this->displayValue = [$this->storageValue];
            }
        } else {
            $this->displayValue = [];
        }
    }
}

