<?php

class validDateValidator extends validator
{
    public function execute($formValue)
    {
        $result = false;
        $formValue = trim($formValue);
        if (strlen($formValue) == 0) {
            $result = true;
        } else {
            $pattern = '#^(0[1-9]|[12][0-9]|3[01])[- /.](0[1-9]|1[012])[- /.](19|20)\d\d$#';
            if (preg_match($pattern, $formValue)) {
                $result = true;
            }
        }

        return $result;
    }
}

