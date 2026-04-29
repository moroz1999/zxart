<?php

class fileNameDataChunk extends DataChunk implements ElementStorageValueHolderInterface
{
    use ElementStorageValueDataChunkTrait;

    public function convertStorageToDisplay()
    {
        $value = html_entity_decode($this->storageValue ?? '', ENT_QUOTES);
        $value = urldecode($value);
        $this->displayValue = str_ireplace('/', '-', $value);
    }

    public function convertStorageToForm()
    {
        $this->formValue = $this->storageValue;
    }

    public function convertFormToStorage()
    {
        $this->setStorageValue(urlencode($this->formValue));
    }

    public function setExternalValue($value)
    {
        $this->storageValue = urlencode(urldecode($value));
        $this->formValue = null;
        $this->convertStorageToDisplay();
    }
}

