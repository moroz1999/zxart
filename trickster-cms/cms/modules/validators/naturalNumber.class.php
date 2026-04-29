<?php

class naturalNumberValidator extends validator
{
    public function execute($formValue)
    {
        $formValue = intval($formValue);
        if ($formValue > 0) {
            return true;
        } else {
            return false;
        }
    }
}

