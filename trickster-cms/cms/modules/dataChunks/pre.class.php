<?php

class preDataChunk extends DataChunk implements ElementStorageValueHolderInterface
{
    use ElementStorageValueDataChunkTrait;

    public function convertStorageToDisplay()
    {
        if ($this->storageValue) {
            $this->displayValue = "<pre>" . $this->storageValue . "</pre>";
        } else {
            $this->displayValue = '';
        }
    }

    public function convertStorageToForm()
    {
        $this->formValue = $this->storageValue;
    }

    public function convertFormToStorage()
    {
        $this->setStorageValue(htmlspecialchars($this->formValue, ENT_QUOTES));
    }

    public function setExternalValue($value)
    {
        $this->storageValue = strip_tags($value);
        $this->formValue = null;
        $this->convertStorageToDisplay();
    }
}


