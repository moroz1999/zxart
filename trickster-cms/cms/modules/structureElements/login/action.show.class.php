<?php

use App\Users\CurrentUserService;

class showLogin extends structureElementAction
{
    /**
     * @param loginElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $structureElement->hidden = true;
        $currentUserService = $this->getService(CurrentUserService::class);
        $user = $currentUserService->getCurrentUser();
        $renderer = $this->getService(renderer::class);

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



