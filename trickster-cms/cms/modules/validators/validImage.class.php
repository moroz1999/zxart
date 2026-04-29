<?php

class validImageValidator extends validator
{
    public function execute($formValue)
    {
        $validated = true;
        if (is_array($formValue)) {
            $typesArray = ['image/x-png', 'image/png', 'image/bmp', 'image/pjpeg', 'image/jpeg', 'image/gif', 'image/webp', 'image/svg+xml'];

            $validated = false;

            $error = $formValue['error'];
            $size = $formValue['size'];
            $type = $formValue['type'];

            if (in_array($type, $typesArray)) {
                if ($error == '0') {
                    if ($size <= 1024 * 1024 * 10) {
                        $validated = true;
                    }
                }
            }
        }

        return $validated;
    }
}

