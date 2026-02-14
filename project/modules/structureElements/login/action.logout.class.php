<?php

use App\Users\CurrentUserService;

class logoutLogin extends structureElementAction
{
    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $currentUserService = $this->getService(CurrentUserService::class);
        $user = $currentUserService->getCurrentUser();
        $user->logout();
        $controller->redirect($this->getRedirectDestination($structureManager, $controller));
    }

    protected function getRedirectDestination(structureManager $structureManager, controller $controller)
    {
        if (stripos($_SERVER['HTTP_REFERER'], $controller->domainURL) === 0) {
            $destination = $_SERVER['HTTP_REFERER'];
        } elseif ($firstPageElement = $structureManager->getElementByMarker('firstpage', $this->getService('LanguagesManager')
            ->getCurrentLanguageId())
        ) {
            $destination = $firstPageElement->URL;
        } else {
            $destination = $controller->rootURL;
        }
        return $destination;
    }
}
