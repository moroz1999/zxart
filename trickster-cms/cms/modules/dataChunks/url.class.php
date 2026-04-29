<?php

class urlDataChunk extends DataChunk implements ElementStorageValueHolderInterface
{
    use ElementStorageValueDataChunkTrait;

    public function convertStorageToDisplay()
    {
        $this->displayValue = $this->checkProtocol($this->storageValue);
    }

    public function convertStorageToForm()
    {
        $this->formValue = $this->storageValue;
    }

    public function convertFormToStorage()
    {
        $value = $this->checkProtocol($this->formValue);
        //sometimes url is filled by public user, let's use htmlentities to block XSS attempts
        $value = htmlentities(urldecode($value));
        $this->setStorageValue($value);
    }

    public function setExternalValue($value)
    {
        $this->storageValue = urldecode($value);
        $this->formValue = null;
        $this->convertStorageToDisplay();
    }

    protected function checkProtocol($value)
    {
        $value = $value ? trim($value) : '';
        if ($value != '') {
            if (stripos($value, '//') === false && stripos($value, 'http://') === false && stripos($value, 'https://') === false && substr($value, 0, 1) != '/'
            ) {
                $value = 'http://' . $value;
            }
        }
        return $value;
    }
}