<?php

class jsonValidator extends validator
{
    public function execute($formValue)
    {
        $formValue = trim($formValue);
        // error suppression used as failed json_decode raises warning
        return $formValue === '' || json_decode($formValue) !== null
            || json_last_error() === JSON_ERROR_NONE;
    }
}
