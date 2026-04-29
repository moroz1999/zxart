<?php

class structureNameDataChunk extends DataChunk implements ElementStorageValueHolderInterface
{
    use ElementStorageValueDataChunkTrait;

    public function convertStorageToDisplay()
    {
        $this->displayValue = $this->storageValue;
    }

    public function convertStorageToForm()
    {
        $this->formValue = $this->storageValue;
    }

    public function convertFormToStorage()
    {
        $value = $this->processValue($this->formValue);

        $this->setStorageValue($value);
    }

    public function setExternalValue($value)
    {
        $this->formValue = $value;
        $this->convertFormToStorage();
        $this->convertStorageToDisplay();
    }

    protected function processValue($value)
    {
        return UrlBeautifierHelper::convert($value);
    }
}


