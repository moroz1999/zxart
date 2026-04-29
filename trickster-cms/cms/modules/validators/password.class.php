<?php

class passwordValidator extends validator
{
    public function execute($formValue)
    {
        $result = true;
        $formValue = trim($formValue);
        if ($formValue != '') {
            if (strlen($formValue) == 0) {
                $result = false;
            }
        }
        return $result;
    }
}

