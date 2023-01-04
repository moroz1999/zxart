<?php

class logoutLogin extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $user = $this->getService('user');
        $user->logout();
        $controller->redirect($this->getRedirectDestination($structureManager, $controller));
    }

    protected function getRedirectDestination($structureManager, $controller)
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