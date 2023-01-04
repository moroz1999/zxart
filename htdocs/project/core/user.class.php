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
    protected $groupsIdList;
    protected $userDataObject;
    /** @var \Illuminate\Database\Connection */
    protected $db;
    /**
     * @var privilegesManager
     */
    protected $privilegesManager;

    /**
     * @param privilegesManager $privilegesManager
     */
    public function setPrivilegesManager($privilegesManager)
    {
        $this->privilegesManager = $privilegesManager;
    }

    /**
     * @param ServerSessionManager $serverSessionManager
     */
    public function setServerSessionManager($serverSessionManager)
    {
        $this->serverSessionManager = $serverSessionManager;
    }

    public function setDb($db)
    {
        $this->db = $db;
    }

    /**
     * @var ServerSessionManager
     */
    protected $serverSessionManager;
    protected $userResourceName = "module_user";
    protected $privilegeResourceName = "module_privilege";
    protected $relationsResourceName = "privilege_relations";
    /** @var user */
    protected static $instance;

    /**
     * @return user
     * @deprecated
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new user();
        }
        return self::$instance;
    }

    public function __construct()
    {
        self::$instance = $this;
    }

    public function initialize()
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
                $this->address = $this->userDataObject->address;
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
                $this->IP = $_SERVER['REMOTE_ADDR'];
                $this->authorId = $this->userDataObject->authorId;

                $this->loadPrivileges();
            }
        }
    }

    public function isAdmin()
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

    private function loadPrivileges()
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

    private function checkStoredPrivileges()
    {
        if (!empty($this->storage['userPrivileges'])) {
            $this->privileges = $this->storage['userPrivileges'];
            return true;
        }
        return false;
    }

    private function storePrivileges()
    {
        $this->storage['userPrivileges'] = $this->privileges;
    }

    public function refreshPrivileges()
    {
        $this->deleteStorageAttribute('userPrivileges');
        $this->loadPrivileges();
    }

    public function __destruct()
    {
        $this->writeStorage();
    }

    protected function readStorage()
    {
        if ($storage = $this->serverSessionManager->get('storage')) {
            $this->storage = $storage;
        }
    }

    protected function writeStorage()
    {
        if ($this->storage) {
            $this->serverSessionManager->set('storage', $this->storage);
        } else {
            $this->serverSessionManager->delete('storage', $this->storage);
        }
    }

    public function logout()
    {
        $anonymousId = $this->checkUser('anonymous', null, true);
        $this->switchUser($anonymousId);
    }

    public function rememberUser($userName, $userId)
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

    public function forgetUser()
    {
        setcookie('loginremember_' . $this->serverSessionManager->getSessionName(), '', 0, '/');
    }

    public function checkUser($userName, $password, $ignorePassword = false)
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
            if (password_verify($password, $storedPassword) || $ignorePassword) {
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
            $errorLog = errorLog::getInstance();
            $errorLog->logMessage(__CLASS__, 'Non-unique user "' . $userName . '" in database');
        }
        return $userId;
    }

    public function checkExistance($userName, $email)
    {
        return (!!$this->queryUserData(['userName' => $userName])) || (!!$this->queryUserData(['email' => $email]));
    }

    public function queryUserData($conditions)
    {
        $userCollection = persistableCollection::getInstance($this->userResourceName);
        $records = $userCollection->load($conditions) ?: [];
        foreach ($records as $record) {
            return $record;
        }
        return null;
    }

    public function switchUser($userId, $resetLivePrivileges = true)
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
        self::$instance = null;
        $this->__destruct();
        $this->__construct();
        $this->initialize();

        //todo: remove workaround and update privileges manager
        if ($resetLivePrivileges && $this->privilegesManager) {
            $this->privilegesManager->resetPrivileges();
        }
    }

    public function clearStorage()
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

    public function deleteStorageAttribute($name)
    {
        if (isset($this->storage[$name])) {
            unset($this->storage[$name]);
        }
    }

    public function setStorageAttribute($name, $value)
    {
        $this->storage[$name] = $value;
    }

    public function getStorageAttribute($name)
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

    public function isAuthorized()
    {
        return $this->userName !== 'anonymous';
    }
}