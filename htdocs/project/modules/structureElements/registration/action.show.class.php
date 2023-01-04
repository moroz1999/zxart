<?php

class showRegistration extends structureElementAction
{
    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param registrationElement $structureElement
     * @return mixed|void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $user = $this->getService('user');
        $registeredHere = $user->userName != 'anonymous' && $structureElement->type == 'registration';

        /**
         * @var ServerSessionManager $serverSessionManager
         */
        $serverSessionManager = $this->getService('ServerSessionManager');
        if ($registeredHere && ($controller->getParameter('success') || ($serverSessionManager->get('showSuccessMessage' . $structureElement->id)))) {
            $serverSessionManager->delete('showSuccessMessage' . $structureElement->id);
            $translationsManager = $this->getService('translationsManager');
            if ($structureElement->type == 'registration') {
                $structureElement->resultMessage = $translationsManager->getTranslationByName('userdata.registrationsuccess');
            } else {
                $structureElement->resultMessage = $translationsManager->getTranslationByName('userdata.userupdatesuccess');
            }
            $structureElement->setViewName('result');
        } else {
            $structureElement->setViewName('form');

            if ($structureElement->requested) {
                if ($structureElement->type == 'userdata' && $user->userName != 'anonymous') {
                    $dynamicFieldsData = [];
                    if ($userElement = $structureManager->getElementById($user->id, null, true)) {
                        $additionalDataRecords = $userElement->getAdditionalData();
                        foreach ($structureElement->getConnectedFields() as $field) {
                            if (isset($additionalDataRecords[$field->id])) {
                                $dynamicFieldsData[$field->id] = $additionalDataRecords[$field->id]->value;
                            } elseif ($field->autocomplete && $userElement->{$field->autocomplete}) {
                                $dynamicFieldsData[$field->id] = $userElement->{$field->autocomplete};
                            }
                        }
                    }
                    $structureElement->setFormValue('dynamicFieldsData', $dynamicFieldsData);
                    $structureElement->setFormValue('company', $user->company);
                    $structureElement->setFormValue('firstName', $user->firstName);
                    $structureElement->setFormValue('lastName', $user->lastName);
                    $structureElement->setFormValue('address', $user->address);
                    $structureElement->setFormValue('city', $user->city);
                    $structureElement->setFormValue('postIndex', $user->postIndex);
                    $structureElement->setFormValue('country', $user->country);
                    $structureElement->setFormValue('email', $user->email);
                    $structureElement->setFormValue('phone', $user->phone);
                    $structureElement->setFormValue('website', $user->website);

                    $structureElement->setFormValue('subscribe', $user->subscribe);

                    $structureElement->setFormValue('userName', $user->userName);
                }
            }
            $lastSocialAction = $user->getStorageAttribute('lastSocialAction');
            $socialActionSuccess = $user->getStorageAttribute('socialActionSuccess');
            if ($lastSocialAction === 'connect') {
                $structureElement->socialConnectionSuccess = $socialActionSuccess;
                if ($socialActionSuccess === false) {
                    $structureElement->errorMessage = $user->getStorageAttribute('socialActionMessage');
                }
                $user->deleteStorageAttribute('socialActionMessage');
                $user->deleteStorageAttribute('socialActionSuccess');
                $user->deleteStorageAttribute('lastSocialAction');
            }
        }
    }
}