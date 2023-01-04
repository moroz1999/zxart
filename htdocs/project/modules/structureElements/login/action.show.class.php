<?php

class showLogin extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $structureElement->hidden = true;
        $user = $this->getService('user');
        $renderer = $this->getService('renderer');

        if ($user->userName == 'anonymous') {
            $structureElement->setViewName('form');

            $lastSocialAction = $user->getStorageAttribute('lastSocialAction');
            $socialActionSuccess = $user->getStorageAttribute('socialActionSuccess');
            if ($lastSocialAction === 'login' && $socialActionSuccess === false) {
                $structureElement->errorMessage = $user->getStorageAttribute('socialActionMessage');
                $user->deleteStorageAttribute('socialActionMessage');
                $user->deleteStorageAttribute('socialActionSuccess');
                $user->deleteStorageAttribute('lastSocialAction');
            }
        } else {
            $structureElement->setViewName('status');
        }
        $renderer->assign('loginForm', $structureElement);
    }
}