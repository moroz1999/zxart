<?php

class checkboxCheckedValidator extends validator
{
    public function execute($formValue)
    {
        if ($formValue) {
            return true;
        }
        return false;
    }
}

