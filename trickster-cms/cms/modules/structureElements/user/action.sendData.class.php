<?php

class sendDataUser extends structureElementAction
{
    /**
     * @param userElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated === true) {
            $structureElement->generatePassword();

            $data = [
                "email" => $structureElement->email,
                "userName" => $structureElement->userName,
                "password" => $structureElement->password,
            ];

            $translationsManager = $this->getService(translationsManager::class);
            $emailDispatcher = $this->getService(EmailDispatcher::class);
            $newDispatchment = $emailDispatcher->getEmptyDispatchment();
            $settings = $this->getService(settingsManager::class)->getSettingsList();
            $newDispatchment->setFromName($settings['default_sender_name'] ? $settings['default_sender_name'] : "");
            $newDispatchment->setFromEmail($settings['default_sender_email'] ? $settings['default_sender_email'] : "");
            $newDispatchment->setSubject($translationsManager->getTranslationByName("email.userdata_subject"));
            $newDispatchment->setData($data);
            $newDispatchment->setDataLifeTime(60);
            $newDispatchment->setReferenceId($structureElement->id);
            $newDispatchment->setType("userData");
            $newDispatchment->registerReceiver($structureElement->email, null);

            if ($emailDispatcher->startDispatchment($newDispatchment)) {
                $structureElement->persistElementData();
                $structureElement->resultMessage = $translationsManager->getTranslationByName('userdata.emailsendingsuccess');
            } else {
                $structureElement->errorMessage = $translationsManager->getTranslationByName('userdata.emailsendingfailed');
            }
        }
        $structureElement->executeAction("showForm");
    }

    public function setValidators(&$validators)
    {
    }

    public function setExpectedFields(&$expectedFields)
    {
    }
}

