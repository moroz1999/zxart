<?php

class serializedIndexDataChunk extends DataChunk implements ElementStorageValueHolderInterface
{
    use ElementStorageValueDataChunkTrait;

    public function convertStorageToForm()
    {
        if ($this->storageValue) {
            $this->formValue = unserialize($this->storageValue);
        }
    }

    public function convertFormToStorage()
    {
        if (is_array($this->formValue)) {
            $list = $this->formValue;
            foreach ($list as $key => &$value) {
                if ($key === '') {
                    unset($list[$key]);
                }
            }
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
        } else {
            $this->displayValue = [];
        }
    }
}

