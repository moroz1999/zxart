<?php

use App\Users\CurrentUserService;

class verifyEmailRegistration extends structureElementAction
{
    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $translationsManager = $this->getService(translationsManager::class);
        $controller = controller::getInstance();
        $email = trim($controller->getParameter('email'));
        $hash = $controller->getParameter('key');

        $secret = $this->getService(ConfigManager::class)->get('emails.dispatchmentSecret');
        $control = hash_hmac('sha256', $email, $secret);

        $result = false;
        if ($email && ($hash === $control)) {
            $db = $this->getService('db');
            $userId = $db->table('module_user')
                ->where('email', '=', $email)
                ->where('verified', '=', '0')
                ->value('id');
            if ($userId) {
                $db->table('module_user')
                    ->where('id', '=', $userId)
                    ->update(['verified' => 1]);
                $currentUserService = $this->getService(CurrentUserService::class);
                $user = $currentUserService->getCurrentUser();
                $user->switchUser($userId, false);
                $result = true;
                $structureElement->resultMessage = $translationsManager->getTranslationByName('userdata.emailverified');
            }
        }

        if (!$result) {
            $structureElement->resultMessage = $translationsManager->getTranslationByName('userdata.emailnotverified');
        }

        $structureElement->setViewName('verify');
    }
}




