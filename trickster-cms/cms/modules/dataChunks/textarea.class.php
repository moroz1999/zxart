<?php

class textareaDataChunk extends DataChunk implements ElementStorageValueHolderInterface
{
    use ElementStorageValueDataChunkTrait;

    public function convertStorageToDisplay()
    {
        $this->displayValue = $this->storageValue ? str_replace("\r\n", "", nl2br($this->storageValue)) : $this->storageValue;
    }

    public function convertStorageToForm()
    {
        $this->formValue = $this->storageValue ? htmlspecialchars($this->storageValue, ENT_QUOTES) : $this->storageValue;
    }

    public function convertFormToStorage()
    {
        $this->setStorageValue(htmlspecialchars($this->formValue, ENT_QUOTES));
    }

    public function setExternalValue($value)
    {
        $this->storageValue = str_ireplace('<br />', "\r\n", $value);
        $this->formValue = null;
        $this->convertStorageToDisplay();
    }
}


