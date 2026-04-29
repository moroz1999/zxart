<?php

class emailValidator extends validator
{
    public function execute($formValue)
    {
        $formValue = trim($formValue);
        return strlen($formValue) == 0
            || filter_var($formValue, FILTER_VALIDATE_EMAIL);
    }
}

