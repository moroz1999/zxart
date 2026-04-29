<?php

class passwordReminderElement extends structureElement
{
    public $dataResourceName = 'module_passwordreminder';
    public $defaultActionName = 'show';
    public $role = 'content';
    public $resultMessage;
    public $errorMessage;
    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['content'] = 'html';
        $moduleStructure['successMessage'] = 'html';
        $moduleStructure['failMessage'] = 'html';
        $moduleStructure['email'] = 'text';
        $moduleStructure['newpassword'] = 'text';
        $moduleStructure['newpasswordrepeat'] = 'text';
    }

    public function generatePassword($length = 8)
    {
        $password = "";
        $possible = '0123456789' . 'abcdefghjklmnopqrstuvwxyz' . 'ABCDEFGHJKLMNOPQRSTUVWXYZ' . '-._+/*$#€@=()';

        // set up a counter
        $i = 0;

        // add random characters to $password until $length is reached
        while ($i < $length) {
            $char = substr($possible, mt_rand(0, strlen($possible) - 1), 1);

            // we don't want this character if it's already in the password
            if (!strstr($password, $char)) {
                $password .= $char;
                $i++;
            }
        }

        return $password;
    }
}
