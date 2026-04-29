<?php

class notEmptyValidator extends validator
{
    public function execute($formValue)
    {
        if (is_array(($formValue))) {
            if (count($formValue)) {
                return true;
            }
            return false;
        }
        $formValue = trim($formValue);
        if (strlen($formValue) > 0) {
            return true;
        } else {
            return false;
        }
    }
}

