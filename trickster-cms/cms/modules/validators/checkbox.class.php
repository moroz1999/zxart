<?php

class checkboxValidator extends validator
{
    public function execute($formValue)
    {
        $validated = false;

        if ($formValue == '0' || $formValue == '1') {
            $validated = true;
        }

        return $validated;
    }
}

