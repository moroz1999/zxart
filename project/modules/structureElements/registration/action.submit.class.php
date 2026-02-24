<?php

use App\Users\CurrentUserService;

class submitRegistration extends structureElementAction
{
    use AjaxFormTrait;
    protected $loggable = true;

    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $currentUserService = $this->getService(CurrentUserService::class);
        $user = $currentUserService->getCurrentUser();
        $translationsManager = $this->getService(translationsManager::class);

        if ($structureElement->type == 'userdata' && $user->userName == 'anonymous') {
            exit();
        }

        $structureElement->setViewName('form');
        $newUser = ($structureElement->type == 'registration' && $user->userName == 'anonymous');

        $coreFields = [];
        $additionalFields = [];
        $connectedFields = $structureElement->getConnectedFields();
        foreach ($connectedFields as &$connectedField) {
            $fieldValue = isset($structureElement->dynamicFieldsData[$connectedField->id]) ? trim($structureElement->dynamicFieldsData[$connectedField->id]) : '';
            $connectedField->value = $fieldValue;
            if (!$connectedField->validate()) {
                $this->validated = false;
                $structureElement->setDynamicFieldError($connectedField->id);
            }
            if ($connectedField->autocomplete) {
                $coreFields[$connectedField->autocomplete] = $connectedField;
            } elseif ($fieldValue) {
                $additionalFields[] = $connectedField;
            }
        }
        $emailToCheck = false;
        if ($this->validated) {
            if (isset($coreFields['email'])) {
                $emailToCheck = $coreFields['email']->value;
            } else {
                $this->validated = false;
            }
        }
        if ($this->validated && $newUser && !isset($coreFields['password'])) {
            $this->validated = false;
        }
        if ($this->validated && isset($coreFields['password']) && isset($coreFields['passwordRepeat'])) {
            if (!$coreFields['passwordRepeat']) {
                $this->validated = false;
            } elseif ($coreFields['password']->value != $coreFields['passwordRepeat']->value) {
                $structureElement->errorMessage = $translationsManager->getTranslationByName('userdata.passwordsnotmatch');
                $structureElement->setDynamicFieldError($coreFields['password']->id);
                $structureElement->setDynamicFieldError($coreFields['passwordRepeat']->id);
                $this->validated = false;
            }
        }
        if ($this->validated && ($newUser || $user->userName != $coreFields['userName']->value)) {
            if ($user->checkExistance($coreFields['userName']->value, $coreFields['email']->value)) {
                $this->validated = false;
                $structureElement->setDynamicFieldError($coreFields['userName']->id);
                $structureElement->setDynamicFieldError($coreFields['email']->id);
                $structureElement->errorMessage = $translationsManager->getTranslationByName('userdata.userexists');
            }
        }
        if ($this->validated && $this->validateAjaxRequest()) {
            $spamChecker = $this->getService(SpamChecker::class);
            if ($emailToCheck && !$spamChecker->checkEmail($emailToCheck)) {
                $structureElement->errorMessage = $translationsManager->getTranslationByName('userdata.bad_email');
            } else {
                $userDbFields = [
                    'company',
                    'firstName',
                    'lastName',
                    'userName',
                    'password',
                    'address',
                    'email',
                    'phone',
                    'address',
                    'city',
                    'postIndex',
                    'country',
                    'website',
                ];
                $mainData = [];
                foreach ($userDbFields as &$fieldName) {
                    if (isset($coreFields[$fieldName])) {
                        $mainData[$fieldName] = $coreFields[$fieldName]->value;
                    }
                }
                //password can be empty if existing user doesn't want to change it
                if (isset ($mainData['password']) && $mainData['password'] == '') {
                    unset($mainData['password']);
                }
                $mainData['structureName'] = $mainData['userName'];

                $usersElementId = $structureManager->getElementIdByMarker("users");
                if ($usersElement = $structureManager->getElementById($usersElementId, $structureElement->getId(), true)) {
                    /**
                     * @var userElement $userElement
                     */
                    if ($newUser) {
                        $userElement = $structureManager->createElement('user', 'show', $usersElement->getId());
                    } else {
                        $userElement = $structureManager->getElementById($user->id);
                    }
                    if ($userElement) {
                        $userElement->prepareActualData();
                        if ($userElement->importExternalData($mainData)) {
                            $userElement->persistElementData();
                            if ($additionalFields) {
                                $userElement->persistAdditionalDataFromFields($additionalFields);
                            }

                            if ($newUser) {
                                $linksManager = $this->getService(linksManager::class);
                                $connectedUserGroupsIds = $structureElement->getConnectedUserGroupsIds();
                                foreach ($connectedUserGroupsIds as &$connectedUserGroupId) {
                                    $linksManager->linkElements($connectedUserGroupId, $userElement->getId(), 'userRelation');
                                }
                                $socialId = $user->getStorageAttribute('socialId');
                                $socialType = $user->getStorageAttribute('socialType');
                                if ($socialId && $socialType) {
                                    $this->getService(SocialDataManager::class)
                                        ->addSocialUser($socialType, $socialId, $userElement->getId());
                                }
                            }
                            foreach ($mainData as $fieldName => $fieldValue) {
                                $structureElement->$fieldName = $mainData[$fieldName];
                            }
                            $structureElement->executeAction('sendEmail');
                            if ($newUser && isset($coreFields['password'])) {
                                $structureElement->resultMessage = $translationsManager->getTranslationByName('userdata.registrationsuccess');
                            } else {
                                $structureElement->resultMessage = $translationsManager->getTranslationByName('userdata.userupdatesuccess');
                            }

                            $this->ajaxFormSuccess = true;
                        }
                    }
                }
            }
        } elseif (!$structureElement->errorMessage) {
            $structureElement->errorMessage = $translationsManager->getTranslationByName('userdata.bad_form_data');
        }
        $reset = true;
        if ($structureElement->type == 'userdata') {
            $reset = false;
        }
        $this->sendAjaxFormResponse($structureElement, $reset);
    }

    public function setValidators(&$validators): void
    {
    }

    public function setExpectedFields(&$expectedFields): void
    {
        $expectedFields = [
            'subscribe',
            'dynamicFieldsData',
        ];
    }
}



