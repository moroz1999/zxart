<?php

use App\Users\CurrentUserService;

class userElement extends structureElement implements JsonDataProvider
{
    use JsonDataProviderElement;
    public $dataResourceName = 'module_user';
    public $defaultActionName = 'show';
    public $role = 'content';
    public array $userGroupsList = [];
    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['userName'] = 'text';
        $moduleStructure['password'] = 'password';

        $moduleStructure['userGroups'] = 'numbersArray';

        $moduleStructure['website'] = 'url';
        $moduleStructure['company'] = 'text';
        $moduleStructure['firstName'] = 'text';
        $moduleStructure['lastName'] = 'text';
        $moduleStructure['address'] = 'text';
        $moduleStructure['city'] = 'text';
        $moduleStructure['postIndex'] = 'text';
        $moduleStructure['country'] = 'text';
        $moduleStructure['email'] = 'email';
        $moduleStructure['phone'] = 'text';
        $moduleStructure['subscribe'] = 'checkbox';
        $moduleStructure['additionalData'] = 'array';
    }

    public function getAdditionalData()
    {
        $collection = persistableCollection::getInstance('module_user_additional_data');
        return $collection->load(['userId' => $this->id], [], 'fieldId');
    }

    public function getAdditionalDataFields()
    {
        $fields = [];
        if ($records = $this->getAdditionalData()) {
            $structureManager = $this->getService('structureManager');
            foreach ($records as $fieldId => $record) {
                if ($element = $structureManager->getElementById($fieldId)) {
                    $element->value = $record->value;
                    $fields[] = $element;
                }
            }
        }
        return $fields;
    }

    public function persistAdditionalDataFromFields($fields)
    {
        $collection = persistableCollection::getInstance('module_user_additional_data');
        $existingData = $this->getAdditionalData();
        foreach ($fields as &$field) {
            if (!isset($existingData[$field->id])) {
                $newRecord = $collection->getEmptyObject();
                $newRecord->userId = $this->id;
                $newRecord->fieldId = $field->id;
                $newRecord->value = $field->value;
                $newRecord->persist();
            } elseif ($existingData[$field->id]->value != $field->value) {
                $existingData[$field->id]->value = $field->value;
                $existingData[$field->id]->persist();
            }
        }
    }

    public function generatePassword($length = 8)
    {
        $password = "";
        $possible = '0123456789' . 'abcdefghjkmnpqqrstuvwxyz' . 'ABCDEFGHJKMNPQQRSTUVWXYZ' . '-._+/*$#€@=()';

        // set up a counter
        $i = 0;

        // add random characters to $password until $length is reached
        while ($i < $length) {
            $char = substr($possible, mt_rand(0, strlen($possible) - 1), 1);

            // we don't want this character if it's already in the password
            if (!strstr($password, $char)) {
                $password .= $char;
                $i++;
            }
        }

        $this->password = password_hash($password, PASSWORD_DEFAULT);
        $this->passwordText = $password;
    }

    /**
     * @param $subscribeOrNot
     * @deprecated - use NewsMailSubscription service instead!
     */
    public function checkSubscription($subscribeOrNot)
    {
        if ($email = $this->email) {
            /**
             * @var structureManager $structureManager
             */
            $structureManager = $this->getService('structureManager');

            $linksManager = $this->getService(linksManager::class);

            $collection = persistableCollection::getInstance('module_newsmailaddress');

            $columns = ['id'];

            $conditions = [];
            $conditions[] = ['column' => 'email', 'action' => '=', 'argument' => $email];

            $mailExists = false;
            $result = $collection->conditionalLoad($columns, $conditions);
            if (is_array($result) && count($result) > 0) {
                $mailExists = true;
            }
            $this->subscribe = $subscribeOrNot;
            $this->persistElementData();

            if (!$mailExists && $subscribeOrNot) {
                if ($mailsElementId = $structureManager->getElementIdByMarker('newsMailsAddresses')) {
                    if ($mailsElement = $structureManager->getElementById($mailsElementId, null, true)) {
                        if ($newAddress = $structureManager->createElement('newsMailAddress', 'showForm', $mailsElementId)
                        ) {
                            $newAddress->prepareActualData();

                            $newData = [];
                            $newData['structureName'] = $email;
                            $newData['email'] = $email;

                            if ($newAddress->importExternalData($newData)) {
                                $newAddress->persistElementData();

                                $currentUserService = $this->getService(CurrentUserService::class);
                                $user = $currentUserService->getCurrentUser();
                                $subscribed = true;
                                $user->setStorageAttribute('subscribed', $subscribed);

                                $groupId = false;
                                $collection = persistableCollection::getInstance('structure_elements');

                                $columns = ['id'];

                                $conditions = [];
                                $conditions[] = [
                                    'column' => 'marker',
                                    'action' => '=',
                                    'argument' => 'newsmail_registered',
                                ];

                                $result = $collection->conditionalLoad($columns, $conditions, [], 1);
                                foreach ($result as &$row) {
                                    $groupId = $row['id'];
                                    break;
                                }

                                if ($groupId) {
                                    $linksManager->linkElements($groupId, $newAddress->id, 'newsmailGroup');
                                }
                            }
                        }
                    }
                }
            } elseif ($mailExists && !$subscribeOrNot) {
                if ($mailsElementId = $structureManager->getElementIdByMarker('newsMailsAddresses')) {
                    if ($structureManager->getElementById($mailsElementId, $this->id, true)) {
                        foreach ($result as &$row) {
                            if ($address = $structureManager->getElementById($row['id'], $mailsElementId, true)) {
                                $address->deleteElementData();
                            }
                        }
                    }
                }
            }
        }
    }

    public function getTitle()
    {
        if ($this->userName) {
            return $this->userName;
        } elseif ($this->email) {
            return $this->email;
        } elseif ($this->firstName) {
            return $this->firstName . ' ' . $this->lastName;
        } else {
            return parent::getTitle();
        }
    }

    public function deleteElementData()
    {
        parent::deleteElementData();
    }
}



