<?php

use App\Users\CurrentUserService;
use ZxArt\UserPreferences\Domain\PreferenceCode;
use ZxArt\UserPreferences\UserPreferencesService;

class loginLogin extends structureElementAction
{
    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $validated = false;
        $userName = $structureElement->userName;
        if ($this->validated === true && $userName != "crontab") {
            $currentUserService = $this->getService(CurrentUserService::class);
            $user = $currentUserService->getCurrentUser();
            $password = $structureElement->getFormValue('password');
            if ($userId = $user->checkUser($userName, $password)) {
                $validated = true;
                $structureElement->setViewName('result');
                $user->switchUser($userId);
                if ($controller->getApplicationName() != "admin") {
                    if ($structureElement->remember == '1') {
                        $currentUserService = $this->getService(CurrentUserService::class);
                        $currentUserService->getCurrentUser()->rememberUser($userName, $userId);
                    } else {
                        $user->forgetUser(); // remove 'remember' cookie from possible previous login
                    }
                }
                $redirectURL = $this->resolveRedirectUrl($controller, $structureManager->getCurrentElement());
                $controller->redirect($redirectURL);
            } else {
                $structureElement->setFormError('password', true);
            }
        }

        if (!$validated) {
            $structureElement->errorMessage = $this->getService(translationsManager::class)
                ->getTranslationByName('login.wrong_credentials', 'public_translations');
            $applicationName = $controller->getApplicationName();
            if ($applicationName == 'admin') {
                $structureElement->executeAction('showForm');
            } else {
                $structureElement->executeAction('show');
            }
        }
    }

    private function resolveRedirectUrl($controller, ?structureElement $currentElement): string
    {
        $defaultUrl = $controller->fullURL;

        if ($currentElement === null) {
            return $defaultUrl;
        }

        try {
            $preferredCode = $this->getService(UserPreferencesService::class)
                ->getPreference(PreferenceCode::LANGUAGE->value);

            if ($preferredCode === null) {
                return $defaultUrl;
            }

            if ($preferredCode === $this->getService(LanguagesManager::class)->getCurrentLanguageCode()) {
                return $defaultUrl;
            }

            return $this->getService(LanguageLinksService::class)
                ->getLinkForLanguage($currentElement, $preferredCode)
                ?? $defaultUrl;
        } catch (Throwable $e) {
            ErrorLog::getInstance()->logMessage('loginLogin::resolveRedirectUrl', $e->getMessage() . "\n" . $e->getTraceAsString());
            return $defaultUrl;
        }
    }

    public function setValidators(&$validators): void
    {
        $validators['userName'][] = 'notEmpty';
        $validators['password'][] = 'notEmpty';
        $validators['password'][] = 'password';
    }

    public function setExpectedFields(&$expectedFields): void
    {
        $expectedFields = [
            'userName',
            'password',
            'remember',
        ];
    }
}



