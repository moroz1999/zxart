<?php

use App\Paths\PathsManager;
use App\Users\CurrentUserService;

class privilegesManager implements DependencyInjectionContextInterface
{
    use DependencyInjectionContextTrait;
    protected $moduleActionsList = false;
    protected $userId;
    protected $userPrivileges = false;
    protected $privileges = [];
    /** @var privilegesManager */
    protected static $instance = null;

    public function __construct()
    {
        self::$instance = $this;
    }

    /**
     * @return privilegesManager
     * @deprecated
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new privilegesManager();
        }
        return self::$instance;
    }

    public function getPrivileges()
    {
        $privilegeRelationsCollection = persistableCollection::getInstance('privilege_relations');
        return $privilegeRelationsCollection->load();
    }

    public function setPrivilege($userId, $elementId, $module, $action, $type)
    {
        $privilegeRelationsCollection = persistableCollection::getInstance('privilege_relations');
        $searchFields = [
            'userId' => $userId,
            'elementId' => $elementId,
            'module' => $module,
            'action' => $action,
        ];
        $privilegesRelation = $privilegeRelationsCollection->loadObject($searchFields);

        if ($type == 'inherit') {
            if ($privilegesRelation) {
                $privilegesRelation->delete();
            }
        } else {
            if (!$privilegesRelation) {
                $privilegesRelation = $privilegeRelationsCollection->getEmptyObject();
                $privilegesRelation->userId = $userId;
                $privilegesRelation->elementId = $elementId;
                $privilegesRelation->module = $module;
                $privilegesRelation->action = $action;
            }
            if ($type == 'allow' || $type == '1') {
                $privilegesRelation->type = '1';
            } else {
                $privilegesRelation->type = '0';
            }
            $privilegesRelation->persist();
        }
    }

    public function deletePrivilege($userId, $elementId = null, $module = null, $action = null, $type = null)
    {
        if (is_numeric($userId)) {
            $db = $this->getService('db');
            $query = $db->table('privilege_relations')->where('userId', '=', $userId);
            if (is_numeric($elementId)) {
                $query->where('elementId', '=', $elementId);
            }
            if ($module) {
                $query->where('module', '=', $module);
            }
            if ($action) {
                $query->where('action', '=', $action);
            }
            if ($type == 'allow' || $type == '1') {
                $query->where('type', '=', 1);
            } else {
                $query->where('type', '=', 0);
            }

            $query->delete();
        }
    }

    public function copyPrivileges($sourceId, $targetId)
    {
        if ($privileges = $this->getElementOwnPrivileges($sourceId)) {
            foreach ($privileges as &$privilege) {
                $this->setPrivilege($privilege->userId, $targetId, $privilege->module, $privilege->action, $privilege->type);
            }
        }
    }

    protected function getElementOwnPrivileges($elementId)
    {
        $privilegeRelationsCollection = persistableCollection::getInstance('privilege_relations');
        $searchFields = [
            'elementId' => $elementId,
        ];
        return $privilegeRelationsCollection->load($searchFields);
    }

    public function compileElementPrivileges($elementId, $parentID)
    {
        if (isset($this->privileges[$parentID])) {
            $this->privileges[$elementId] = $this->privileges[$parentID];
        }
        if (!isset($this->privileges[$elementId])) {
            $this->privileges[$elementId] = [];
        }
        $userPrivileges = $this->getUserPrivileges();
        if (isset($userPrivileges[$elementId])) {
            foreach ($userPrivileges[$elementId] as $elementType => &$typeActions) {
                foreach ($typeActions as $actionName => &$type) {
                    if ($type == '1') {
                        $this->privileges[$elementId][$elementType][$actionName] = true;
                    } elseif ($type == '0' && isset($this->privileges[$elementId][$elementType])) {
                        if (isset($this->privileges[$elementId][$elementType][$actionName])) {
                            unset($this->privileges[$elementId][$elementType][$actionName]);

                            //if this was the last action for this element type, we should delete the whole array to
                            //prevent the type without actions appearing in allowedTypes in loadElements
                            if (!$this->privileges[$elementId][$elementType]) {
                                unset($this->privileges[$elementId][$elementType]);
                            }
                        }
                    }
                }
            }
        }
        return $this->privileges[$elementId];
    }

    public function getUserPrivileges()
    {
        $currentUserService = $this->getService(CurrentUserService::class);
        $user = $currentUserService->getCurrentUser();
        if (!$this->userPrivileges) {
            $this->userPrivileges = $user->privileges;
        }
        return $this->userPrivileges;
    }

    public function checkPrivilegesForAction($elementID, $actionName, $structureType)
    {
        $validated = false;

        if (isset($this->privileges[$elementID][$structureType][$actionName])) {
            if ($this->privileges[$elementID][$structureType][$actionName] === true) {
                $validated = true;
            }
        }
        return $validated;
    }

    public function getElementPrivileges($elementId)
    {
        if (!isset($this->privileges[$elementId])) {
            //todo: implement active privileges compilation
            //should now work passively
            //after implementation make compileElementPrivileges protected(!)
        }
        return $this->privileges[$elementId];
    }

    public function reRegisterElement($oldId, $newId)
    {
        if (isset($this->privileges[$oldId])) {
            $element = $this->privileges[$oldId];
            $this->privileges[$newId] = $element;
            unset($element);
        }
    }

    public function getAllowedElements($elementId, $idList)
    {
        $allowedElements = false;
        if (isset($this->privileges[$elementId])) {
            $allowedElements = array_keys($this->privileges[$elementId]);
        }
        foreach ($idList as &$childId) {
            if (isset($this->userPrivileges[$childId])) {
                $this->compileElementPrivileges($childId, $elementId);
                if (isset($this->privileges[$childId])) {
                    $allowedElements = array_unique(
                        array_merge($allowedElements, array_keys($this->privileges[$childId]))
                    );
                }
            }
        }
        return $allowedElements;
    }

    public function getModuleActionsList()
    {
        if ($this->moduleActionsList == false) {
            $controller = $this->getService(controller::class);
            $pathsManager = $this->getService(PathsManager::class);
            $fileDirectory = $pathsManager->getRelativePath('structureElements');
            foreach ($controller->getIncludePaths() as $path) {
                $this->scanDirectory($path . $fileDirectory);
            }
            ksort($this->moduleActionsList);
        }
        return $this->moduleActionsList;
    }

    protected function scanDirectory($structureElementsPath)
    {
        if (is_dir($structureElementsPath)) {
            if ($directoriesList = scandir($structureElementsPath)) {
                foreach ($directoriesList as &$directory) {
                    if (is_dir($structureElementsPath . $directory)) {
                        if (file_exists($structureElementsPath . $directory . '/structure.actions.php')) {
                            include_once($structureElementsPath . $directory . '/structure.actions.php');
                            if (isset($moduleActions) && is_array($moduleActions)) {
                                if ($directory != "root") {
                                    if (!in_array("showForm", $moduleActions)) {
                                        $moduleActions[] = "showForm";
                                    }
                                    if (!in_array("receive", $moduleActions)) {
                                        $moduleActions[] = "receive";
                                    }
                                }
                                foreach ($moduleActions as &$action) {
                                    $privilege = new moduleActionPrivilege($directory, $action);
                                    $this->moduleActionsList["$directory:$action"] = $privilege;
                                }
                                unset($moduleActions);
                            }
                        }
                    }
                }
            }
        }
    }

    public function resetPrivileges()
    {
        $this->userPrivileges = false;
        $this->privileges = [];
    }
}

class moduleActionPrivilege
{
    public $module;
    public $action;
    public $type;

    public function __construct($module, $action)
    {
        $this->module = $module;
        $this->action = $action;
    }
}





