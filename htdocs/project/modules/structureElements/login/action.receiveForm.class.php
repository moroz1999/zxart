<?php

class receiveFormLogin extends structureElementAction
{
    protected $loggable = true;

    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated === true) {
            $user = $this->getService('user');
            $structureElement->setViewName('result');

            if ($userId = $user->checkUser($structureElement->userName, $structureElement->password)) {
                $user->switchUser($userId);
                $controller->restart($controller->rootURL);
            } else {
                $structureElement->executeAction('showForm');
            }
        } else {
            $structureElement->executeAction('showForm');
        }
    }

    public function setValidators(&$validators)
    {
        $validators['userName'][] = 'notEmpty';
        $validators['password'][] = 'notEmpty';
        $validators['password'][] = 'password';
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = ['userName', 'password'];
    }
}