<?php

class codeDataChunk extends DataChunk implements ElementStorageValueHolderInterface
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
        $this->formValue = preg_replace('/&(?!amp;|quot;|nbsp;|gt;|lt;|laquo;|raquo;|copy;|reg;|bul;|rsquo;)/', '&amp;', $this->formValue ?? '');
        $this->setStorageValue($this->formValue);
    }

    public function setExternalValue($value)
    {
        $this->storageValue = $value;
        $this->formValue = null;
        $this->convertStorageToDisplay();
    }
}


