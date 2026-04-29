<?php

class htmlDataChunk extends DataChunk implements ElementStorageValueHolderInterface
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
        $this->storageValue = $this->processValue($value);
        $this->formValue = null;
        $this->convertStorageToDisplay();
    }

    protected function processValue($value)
    {
        $value = $value ?? '';

        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);
        $value = $purifier->purify($value);

        if (stripos($value, 'img') === false) {
            $emptyTest = strip_tags($value);
            $emptyTest = html_entity_decode($emptyTest, ENT_QUOTES, 'UTF-8');
            $emptyTest = trim($emptyTest, chr(0xC2) . chr(0xA0));
            $emptyTest = preg_replace("#\s#is", "", $emptyTest);

            if ($emptyTest === '') {
                $value = '';
            }
        }

        return $value;
    }
}

