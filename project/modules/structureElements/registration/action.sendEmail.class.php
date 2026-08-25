<?php

use ZxArt\Registration\EmailVerificationTokenService;

class sendEmailRegistration extends structureElementAction
{
    /**
     * @param registrationElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated === true) {
            $translationsManager = $this->getService(translationsManager::class);

            $formData = $structureElement->getFormData();

            $email = $formData['email'] ?: '';
            $fieldsInfo = [];
            foreach ($structureElement->getConnectedFields() as $field) {
                if ($field->autocomplete == 'passwordRepeat' || $field->autocomplete == 'password') {
                    continue;
                }
                $fieldsInfo[] = [
                    'title' => $field->title,
                    'value' => isset($formData['dynamicFieldsData'][$field->id]) ? $formData['dynamicFieldsData'][$field->id] : '',
                ];
            }

            $data = [
                'fields' => $fieldsInfo,
            ];
            if ($structureElement->type == 'registration' && $email) {
                $key = $this->getService(EmailVerificationTokenService::class)->create($email);
                $data['verifyEmail'] = rtrim((string)$controller->baseURL, '/')
                    . '/verify-email?email=' . rawurlencode($email)
                    . '&key=' . rawurlencode($key);
            }
            /**
             * @var $emailDispatcher EmailDispatcher
             */
            $emailDispatcher = $this->getService(EmailDispatcher::class);
            $newDispatchment = $emailDispatcher->getEmptyDispatchment();
            $settings = $this->getService(settingsManager::class)->getSettingsList();
            $newDispatchment->setFromName($settings['default_sender_name'] ?: "");
            $newDispatchment->setFromEmail($settings['default_sender_email'] ?: "");
            $subject = $structureElement->title;
            $newDispatchment->setSubject($subject);
            $newDispatchment->setData($data);
            $newDispatchment->setDataLifeTime(60);
            $newDispatchment->setReferenceId($structureElement->getId());
            $newDispatchment->setType('userData');
            $newDispatchment->registerReceiver($structureElement->email, null);

            if ($emailDispatcher->startDispatchment($newDispatchment)) {
                $structureElement->resultMessage = $translationsManager->getTranslationByName('userdata.emailsendingsuccess');
            } else {
                $structureElement->errorMessage = $translationsManager->getTranslationByName('userdata.emailsendingfailed');
            }
        }
        $structureElement->setViewName('form');
    }
}

