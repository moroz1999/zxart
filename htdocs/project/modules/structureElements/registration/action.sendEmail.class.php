<?php

class sendEmailRegistration extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated === true) {
            $translationsManager = $this->getService('translationsManager');

            $formData = $structureElement->getFormData();

            $email = $formData['email'] ? $formData['email'] : '';
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
                $secret = $this->getService('ConfigManager')->get('emails.dispatchmentSecret');
                $hash = hash_hmac('sha256', $email, $secret);
                $data['verifyEmail'] = $structureElement->getUrl('verifyEmail') . '/email:' . $email . '/key:' . $hash;
            }
            /**
             * @var $emailDispatcher EmailDispatcher
             */
            $emailDispatcher = $this->getService('EmailDispatcher');
            $newDispatchment = $emailDispatcher->getEmptyDispatchment();
            $settings = $this->getService('settingsManager')->getSettingsList();
            $newDispatchment->setFromName($settings['default_sender_name'] ?: "");
            $newDispatchment->setFromEmail($settings['default_sender_email'] ?: "");
            $subject = $structureElement->title;
            $newDispatchment->setSubject($subject);
            $newDispatchment->setData($data);
            $newDispatchment->setDataLifeTime(60);
            $newDispatchment->setReferenceId($structureElement->id);
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

