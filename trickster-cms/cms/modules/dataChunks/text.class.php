<?php

class textDataChunk extends DataChunk implements ElementStorageValueHolderInterface
{
    use ElementStorageValueDataChunkTrait;

    public function setFormValue($value)
    {
        $value = $value ?? '';
        $this->formValue = htmlspecialchars(html_entity_decode(trim($value), ENT_QUOTES), ENT_QUOTES);
    }

    public function convertStorageToDisplay()
    {
        $this->displayValue = $this->storageValue ? trim($this->storageValue) : $this->storageValue;
    }

    public function convertStorageToForm()
    {
        $this->formValue = $this->storageValue;
    }

    public function convertFormToStorage()
    {
        $this->setStorageValue($this->formValue);
    }

    public function setExternalValue($value)
    {
        $this->storageValue = htmlspecialchars(html_entity_decode($value ?? '', ENT_QUOTES), ENT_QUOTES);
        $this->formValue = null;
        $this->convertStorageToDisplay();
    }
}


