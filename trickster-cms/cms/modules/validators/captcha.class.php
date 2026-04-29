<?php

use App\Users\CurrentUserService;

class captchaValidator extends validator
{
    public function execute($formValue)
    {
        $currentUserService = $this->getService(CurrentUserService::class);
        return $formValue && strtolower($formValue) == $currentUserService->getCurrentUser()->getStorageAttribute('last_captcha');
    }
}




