<?php

class userElement extends structureElement
{
    use AuthorElementsProviderTrait;

    public $dataResourceName = 'module_user';
    public $defaultActionName = 'show';
    public $role = 'content';
    protected $authorElement;

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
        $moduleStructure['showemail'] = 'checkbox';
        $moduleStructure['additionalData'] = 'array';
        $moduleStructure['authorId'] = 'text';
        $moduleStructure['newAuthorId'] = 'text';
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
        foreach ($fields as $field) {
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

            $linksManager = $this->getService('linksManager');

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
                        if ($newAddress = $structureManager->createElement(
                            'newsMailAddress',
                            'showForm',
                            $mailsElementId
                        )
                        ) {
                            $newAddress->prepareActualData();

                            $newData = [];
                            $newData['structureName'] = $email;
                            $newData['email'] = $email;

                            if ($newAddress->importExternalData($newData)) {
                                $newAddress->persistElementData();

                                $user = $this->getService('user');
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
                                foreach ($result as $row) {
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
                        foreach ($result as $row) {
                            if ($address = $structureManager->getElementById($row['id'], $mailsElementId, true)) {
                                $address->deleteElementData();
                            }
                        }
                    }
                }
            }
        }
    }

    public function getAuthorIds()
    {
        if ($this->newAuthorId) {
            return [$this->newAuthorId];
        } else {
            return [$this->authorId];
        }
    }

    public function getAuthorElement()
    {
        if ($this->authorElement === null && $this->authorId) {
            $structureManager = $this->getService('structureManager');

            $this->authorElement = $structureManager->getElementById($this->authorId);
        }
        return $this->authorElement;
    }

    public function getAuthorUrl()
    {
        if ($authorElement = $this->getAuthorElement()) {
            return $authorElement->getUrl();
        }
        return false;
    }

    public function getUrl($action = null)
    {
        return $this->getAuthorUrl();
    }

    public function changeConnectedAuthor($newAuthorId)
    {
        if ($this->authorId != $newAuthorId) {
            $privilegesManager = $this->getService('privilegesManager');
            if ($this->authorId) {
                $privilegesManager->deletePrivilege($this->id, $this->authorId, 'author', 'showPublicForm', 'allow');
                $privilegesManager->deletePrivilege($this->id, $this->authorId, 'author', 'publicReceive', 'allow');
                $privilegesManager->deletePrivilege($this->id, $this->authorId, 'author', 'delete', 'allow');
                $privilegesManager->deletePrivilege($this->id, $this->authorId, 'author', 'deleteFile', 'allow');
                $privilegesManager->deletePrivilege($this->id, $this->authorId, 'zxPicture', 'showPublicForm', 'allow');
                $privilegesManager->deletePrivilege($this->id, $this->authorId, 'zxPicture', 'publicReceive', 'allow');
                $privilegesManager->deletePrivilege($this->id, $this->authorId, 'zxPicture', 'delete', 'allow');
                $privilegesManager->deletePrivilege($this->id, $this->authorId, 'zxPicture', 'deleteFile', 'allow');
                $privilegesManager->deletePrivilege($this->id, $this->authorId, 'zxPicture', 'submitTags', 'allow');
                $privilegesManager->deletePrivilege($this->id, $this->authorId, 'zxMusic', 'showPublicForm', 'allow');
                $privilegesManager->deletePrivilege($this->id, $this->authorId, 'zxMusic', 'publicReceive', 'allow');
                $privilegesManager->deletePrivilege($this->id, $this->authorId, 'zxMusic', 'delete', 'allow');
                $privilegesManager->deletePrivilege($this->id, $this->authorId, 'zxMusic', 'deleteFile', 'allow');
                $privilegesManager->deletePrivilege($this->id, $this->authorId, 'zxMusic', 'submitTags', 'allow');
                $privilegesManager->deletePrivilege($this->id, $this->authorId, 'comment', 'delete', 'allow');
            }

            if ($newAuthorId) {
                $privilegesManager->setPrivilege($this->id, $newAuthorId, 'author', 'showPublicForm', 'allow');
                $privilegesManager->setPrivilege($this->id, $newAuthorId, 'author', 'publicReceive', 'allow');
                $privilegesManager->setPrivilege($this->id, $newAuthorId, 'author', 'publicDelete', 'allow');
                $privilegesManager->setPrivilege($this->id, $newAuthorId, 'author', 'deleteFile', 'allow');
                $privilegesManager->setPrivilege($this->id, $newAuthorId, 'zxPicture', 'showPublicForm', 'allow');
                $privilegesManager->setPrivilege($this->id, $newAuthorId, 'zxPicture', 'publicReceive', 'allow');
                $privilegesManager->setPrivilege($this->id, $newAuthorId, 'zxPicture', 'publicDelete', 'allow');
                $privilegesManager->setPrivilege($this->id, $newAuthorId, 'zxPicture', 'deleteFile', 'allow');
                $privilegesManager->setPrivilege($this->id, $newAuthorId, 'zxPicture', 'submitTags', 'allow');
                $privilegesManager->setPrivilege($this->id, $newAuthorId, 'zxMusic', 'showPublicForm', 'allow');
                $privilegesManager->setPrivilege($this->id, $newAuthorId, 'zxMusic', 'publicReceive', 'allow');
                $privilegesManager->setPrivilege($this->id, $newAuthorId, 'zxMusic', 'publicDelete', 'allow');
                $privilegesManager->setPrivilege($this->id, $newAuthorId, 'zxMusic', 'deleteFile', 'allow');
                $privilegesManager->setPrivilege($this->id, $newAuthorId, 'zxMusic', 'submitTags', 'allow');
                $privilegesManager->setPrivilege($this->id, $newAuthorId, 'comment', 'publicDelete', 'allow');
            }
        }
        $this->authorId = $newAuthorId;
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
        $this->getService('SocialDataManager')->removeSocialUser($this->id);
        $structureManager = $this->getService('structureManager');
        $db = $this->getService('db');
        if ($records = $db->table('module_comment')->select('id')->where('userId', '=', $this->id)->get()) {
            $commentIds = array_column($records, 'id');
            foreach ($commentIds as $commentId) {
                if ($commentElement = $structureManager->getElementById($commentId)) {
                    $commentElement->deleteElementData();
                }
            }
        }
        try {
            $db->table('votes_history')->where('userId', '=', $this->id)->delete();
            //            $db->table('actions_log')->where('userId', '=', $this->id)->delete();
            //            $db->table('events_log')->where('userId', '=', $this->id)->delete();
        } catch (Exception $e) {
        }


        parent::deleteElementData();
    }

    public function getElementData()
    {
        return [
            'userId' => $this->id,
            'firstName' => $this->firstName,
            'lastName' => $this->firstName,
        ];
    }

    public function setTrackingCode()
    {
    }
}