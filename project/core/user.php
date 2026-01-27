<?php

class user
{
    public $id;
    public $IP;
    public $userName = "";
    public $privileges = [];
    public $storage = [];
    public $company;
    public $firstName;
    public $lastName;
    public $address;
    public $city;
    public $postIndex;
    public $email;
    public $country;
    public $phone;
    public $website;
    public $userType;
    public $subscribe;
    public $showemail;
    public $authorId;
    public $supporter;
    public $vip;
    public $volunteer;
    protected $groupsIdList;
    protected $userDataObject;

    public function __construct(
        private \Illuminate\Database\Connection $db,
        private privilegesManager               $privilegesManager,
        private ServerSessionManager            $serverSessionManager
    )
    {
        $this->initialize();
    }

    protected $userResourceName = "module_user";
    protected $relationsResourceName = "privilege_relations";

    public function initialize(): void
    {
        $this->readStorage();
        $userId = $this->readUserId();

        $usersCollection = persistableCollection::getInstance($this->userResourceName);

        if ($this->userDataObject = $usersCollection->getPersistableObject(
            [
                'id' => $userId,
                'languageId' => '0',
                'banned' => 0
            ]
        )
        ) {
            if ($this->userDataObject->load()) {
                $this->id = $this->userDataObject->id;
                $this->userName = $this->userDataObject->userName;
                $this->company = $this->userDataObject->company;
                $this->firstName = $this->userDataObject->firstName;
                $this->lastName = $this->userDataObject->lastName;
                $this->city = $this->userDataObject->city;
                $this->postIndex = $this->userDataObject->postIndex;
                $this->country = $this->userDataObject->country;
                $this->email = $this->userDataObject->email;
                $this->phone = $this->userDataObject->phone;
                $this->website = $this->userDataObject->website;
                $this->userType = $this->userDataObject->userType;
                $this->address = $this->userDataObject->address;
                $this->subscribe = $this->userDataObject->subscribe;
                $this->showemail = $this->userDataObject->showemail;
                $this->supporter = $this->userDataObject->supporter;
                $this->vip = $this->userDataObject->vip;
                $this->volunteer = $this->userDataObject->volunteer;
                $this->IP = $_SERVER['REMOTE_ADDR'];
                $this->authorId = $this->userDataObject->authorId;

                $this->loadPrivileges();
            }
        }
    }

    public function isAdmin(): bool
    {
        if (!$this->isAuthorized()) {
            return false;
        }
        $groups = array_flip($this->getGroupMarkers());
        return isset($groups['userGroup-documentsManager']) || isset($groups['userGroup-developer']);
    }

    public function getGroupMarkers()
    {
        static $result;
        if ($result === null) {
            $result = [];
            $groupsIds = $this->getGroupsIdList();
            if ($groupsIds) {
                $result = $this->db->table('structure_elements')
                    ->whereIn('id', $groupsIds)
                    ->pluck('marker');
            }
        }
        return $result;
    }

    private function loadPrivileges(): void
    {
        if (!$this->checkStoredPrivileges()) {
            $relationsDataCollection = persistableCollection::getInstance($this->relationsResourceName);
            $searchFields = ['userId' => $this->id];
            $userRelations = $relationsDataCollection->load($searchFields);

            if ($idList = $this->getGroupsIdList()) {
                foreach ($idList as $userGroupId) {
                    $searchFields = ['userId' => $userGroupId];
                    $groupRelations = $relationsDataCollection->load($searchFields);

                    foreach ($groupRelations as $relationObject) {
                        $elementId = $relationObject->elementId;
                        $type = $relationObject->type;
                        $actionName = $relationObject->action;
                        $elementType = $relationObject->module;
                        if (!isset($this->privileges[$elementId])) {
                            $this->privileges[$elementId] = [];
                        }
                        if (!isset($this->privileges[$elementId][$elementType])) {
                            $this->privileges[$elementId][$elementType] = [];
                        }

                        //Privileges priorities resolution on a same level: Allow > Deny > Inherited
                        if (!isset($this->privileges[$elementId][$elementType][$actionName])) {
                            $this->privileges[$elementId][$elementType][$actionName] = $type;
                        } elseif ($this->privileges[$elementId][$elementType][$actionName] != 1) {
                            $this->privileges[$elementId][$elementType][$actionName] = $type;
                        }
                    }
                }
            }

            foreach ($userRelations as $relationObject) {
                $elementId = $relationObject->elementId;
                $type = $relationObject->type;
                $actionName = $relationObject->action;
                $elementType = $relationObject->module;
                if (!isset($this->privileges[$elementId])) {
                    $this->privileges[$elementId] = [];
                }
                if (!isset($this->privileges[$elementId][$elementType])) {
                    $this->privileges[$elementId][$elementType] = [];
                }

                //Privileges priorities resolution on a same level: Allow > Deny > Inherited
                if (!isset($this->privileges[$elementId][$elementType][$actionName])) {
                    $this->privileges[$elementId][$elementType][$actionName] = $type;
                } elseif ($this->privileges[$elementId][$elementType][$actionName] != 1) {
                    $this->privileges[$elementId][$elementType][$actionName] = $type;
                }
            }
            if ($this->isAuthorized()) {
                $this->storePrivileges();
            }
        }
    }

    private function checkStoredPrivileges(): bool
    {
        if (!empty($this->storage['userPrivileges'])) {
            $this->privileges = $this->storage['userPrivileges'];
            return true;
        }
        return false;
    }

    private function storePrivileges(): void
    {
        $this->storage['userPrivileges'] = $this->privileges;
    }

    public function refreshPrivileges(): void
    {
        $this->deleteStorageAttribute('userPrivileges');
        $this->loadPrivileges();
    }

    public function __destruct()
    {
        $this->writeStorage();
    }

    protected function readStorage(): void
    {
        if ($storage = $this->serverSessionManager->get('storage')) {
            $this->storage = $storage;
        }
    }

    protected function writeStorage(): void
    {
        if ($this->storage) {
            $this->serverSessionManager->set('storage', $this->storage);
        } else {
            $this->serverSessionManager->delete('storage', $this->storage);
        }
    }

    public function logout(): void
    {
        $anonymousId = $this->checkUser('anonymous', null, true);
        $this->switchUser($anonymousId);
    }

    public function rememberUser($userName, $userId): void
    {
        $controller = controller::getInstance();
        $salt = $controller->domainURL;
        $sessionName = $this->serverSessionManager->getSessionName();

        $userCollection = persistableCollection::getInstance($this->userResourceName);
        $elements = $userCollection->load(['userName' => $userName, 'id' => $userId, 'banned' => 0]);

        if (count($elements) == 1) {
            $userDataObject = reset($elements);
            $passwordHash = $userDataObject->password;
            $cookieHash = hash('sha256', $userId . $userName . $sessionName . strrev($salt) . $passwordHash);
            $cookieText = json_encode([$userName, $cookieHash]);
            $cookieLifeTime = 90 * 24 * 60 * 60;
            setcookie('loginremember_' . $sessionName, $cookieText, time() + $cookieLifeTime, '/');
        }
    }

    public function forgetUser(): void
    {
        setcookie('loginremember_' . $this->serverSessionManager->getSessionName(), '', 0, '/');
    }

    /**
     * @psalm-param 'anonymous' $userName
     */
    public function checkUser(string $userName, $password, bool $ignorePassword = false)
    {
        $userId = false;

        $userCollection = persistableCollection::getInstance($this->userResourceName);
        $searchFields = [
            'userName' => $userName,
            'banned' => 0
        ];
        $users = $userCollection->load($searchFields);

        if (!$users) {
            $searchFields = [
                'email' => $userName,
                'banned' => 0
            ];
            $users = $userCollection->load($searchFields);
        }

        if ($users) {
            $user = $users[0];
            $storedPassword = $user->password;
            if (($password !== null && password_verify($password, $storedPassword)) || $ignorePassword) {
                $userDataObject = reset($users);
                if ($userDataObject->verified) {
                    $userId = $userDataObject->id;
                }
            } // TODO: delete this md5 password check in 2020
            elseif ($storedPassword == md5($password)) {
                $userDataObject = reset($users);
                if ($userDataObject->verified) {
                    $userId = $userDataObject->id;
                }
                // renew the hash from md5 to the new password_hash
                $user->password = password_hash($password, PASSWORD_DEFAULT);
                $user->persist();
            }
        } elseif (count($users) > 1) {
            $errorLog = ErrorLog::getInstance();
            $errorLog->logMessage(__CLASS__, 'Non-unique user "' . $userName . '" in database');
        }
        return $userId;
    }

    public function checkExistance($userName, $email): bool
    {
        return ($this->queryUserData(['userName' => $userName])) || ($this->queryUserData(['email' => $email]));
    }

    /**
     * @psalm-param array{userName?: mixed, email?: mixed} $conditions
     */
    public function queryUserData(array $conditions)
    {
        $userCollection = persistableCollection::getInstance($this->userResourceName);
        $records = $userCollection->load($conditions) ?: [];
        foreach ($records as $record) {
            return $record;
        }
        return null;
    }

    public function switchUser($userId, $resetLivePrivileges = true): void
    {
        unset($this->storage['userPrivileges']);
        unset($this->storage['userGroupsIdList']);

        $this->storage['currentUserId'] = $userId;

        $this->id = null;
        $this->IP = null;
        $this->userName = "";
        $this->privileges = [];
        $this->groupsIdList = null;

        $this->writeStorage();
        $this->__destruct();
        $this->initialize();

        //todo: remove workaround and update privileges manager
        if ($resetLivePrivileges && $this->privilegesManager) {
            $this->privilegesManager->resetPrivileges();
        }
    }

    public function clearStorage(): void
    {
        $this->storage = [];
    }

    public function readUserId()
    {
        $userId = false;
        if (isset($this->storage['currentUserId'])) {
            $userId = $this->storage['currentUserId'];
        } else {
            $controller = controller::getInstance();
            $sessionName = $this->serverSessionManager->getSessionName();

            if (isset($_COOKIE['loginremember_' . $sessionName])) {
                $cookieContents = json_decode($_COOKIE['loginremember_' . $sessionName], true);
                if ($cookieContents && count($cookieContents) == 2) {
                    $userName = $cookieContents['0'];
                    $userCollection = persistableCollection::getInstance($this->userResourceName);
                    $elements = $userCollection->load(['userName' => $userName, 'banned' => 0]);

                    if (count($elements) == 1) {
                        $userDataObject = reset($elements);
                        $loadedUserID = $userDataObject->id;
                        $passwordHash = $userDataObject->password;
                        $salt = $controller->domainURL;
                        $cookieHash = hash(
                            'sha256',
                            $loadedUserID . $userName . $sessionName . strrev($salt) . $passwordHash
                        );
                        if ($cookieContents[1] == $cookieHash) {
                            $userId = $loadedUserID;
                        }
                    }
                }
            }
        }
        if (!$userId && $anonymousId = $this->checkUser('anonymous', null, true)) {
            $userId = $anonymousId;
        }
        return $userId;
    }

    public function getGroupsIdList()
    {
        if (is_null($this->groupsIdList)) {
            if (isset($this->storage['userGroupsIdList'])) {
                $this->groupsIdList = $this->storage['userGroupsIdList'];
            } else {
                $this->groupsIdList = [];

                $linksCollection = persistableCollection::getInstance('structure_links');
                $searchFields = [
                    [
                        'childStructureId',
                        '=',
                        $this->id,
                    ],
                    [
                        'type',
                        '=',
                        'userRelation',
                    ],
                ];
                if ($rows = $linksCollection->conditionalLoad('parentStructureId', $searchFields)) {
                    foreach ($rows as $row) {
                        $this->groupsIdList[] = $row['parentStructureId'];
                    }
                }
                if ($this->isAuthorized()) {
                    $this->setStorageAttribute('userGroupsIdList', $this->groupsIdList);
                }
            }
        }

        return $this->groupsIdList;
    }

    public function deleteStorageAttribute(string $name): void
    {
        if (isset($this->storage[$name])) {
            unset($this->storage[$name]);
        }
    }

    public function setStorageAttribute(string $name, mixed $value): void
    {
        $this->storage[$name] = $value;
    }

    /**
     * @psalm-param 'border'|'hidden'|'mode' $name
     */
    public function getStorageAttribute(string $name)
    {
        $value = false;
        if (isset($this->storage[$name])) {
            $value = $this->storage[$name];
        }
        return $value;
    }

    public function getName()
    {
        $name = '';
        if ($this->firstName) {
            $name = $this->firstName;
            if ($this->lastName) {
                $name .= ' ' . $this->lastName;
            }
        } elseif ($this->userName) {
            $name = $this->userName;
        } elseif ($this->email) {
            $name = $this->email;
        }
        return $name;
    }

    public function isAuthorized(): bool
    {
        return $this->userName !== 'anonymous';
    }

    public function hasAds(): bool
    {
        return !$this->vip && !$this->volunteer && !$this->supporter;
    }
}