<?php

use App\Paths\PathsManager;

/**
 * Class persistableCollection
 *
 */
class persistableCollection extends errorLogger implements DependencyInjectionContextInterface
{
    use DependencyInjectionContextTrait;

    /**
     * @var transportObject
     */
    protected $transportObject;
    protected $primaryFields;
    protected $columnNames;

    protected $resourceName;
    /**
     * @var persistableCollection[]
     */
    private static $instancesList = [];

    public static function getInstance($resourceName)
    {
        if (!isset(self::$instancesList[$resourceName])) {
            $controller = controller::getInstance();
            self::$instancesList[$resourceName] = new persistableCollection($resourceName, $controller->getContainer());
        }

        return self::$instancesList[$resourceName];
    }

    public function getPrimaryFields()
    {
        if ($this->primaryFields === null) {

            $resourceName = $this->resourceName;
            $this->transportObject->setResourceName($this->resourceName);

            $keyName = 'primaryFields_' . $resourceName;
            /**
             * @var Cache $cache
             */
            $cache = $this->getService(Cache::class);
            if ($cache->isEnabled()) {
                if (($this->primaryFields = $cache->get($keyName)) === null) {
                    $this->primaryFields = $this->transportObject->loadPrimaryFields();
                    $cache->set($keyName, $this->primaryFields);
                }
            } else {
                /**
                 * @var ServerSessionManager $serverSessionManager
                 */
                $serverSessionManager = $this->getService(ServerSessionManager::class);

                if (!$this->primaryFields = $serverSessionManager->get($keyName)) {
                    $this->primaryFields = $this->transportObject->loadPrimaryFields();
                    $serverSessionManager->set($keyName, $this->primaryFields);
                }
            }

            if (!is_array($this->primaryFields)) {
                $this->logError('Primary field detection problem');
            }
        }

        return $this->primaryFields;
    }

    public function getColumnNames()
    {
        if ($this->columnNames === null) {
            $resourceName = $this->resourceName;
            $this->transportObject->setResourceName($this->resourceName);

            $keyName = 'columnNames_' . $resourceName;
            /**
             * @var Cache $cache
             */
            $cache = $this->getService(Cache::class);
            if ($cache->isEnabled()) {
                if (($this->columnNames = $cache->get($keyName)) === null) {
                    $this->columnNames = $this->transportObject->loadColumnNames();
                    $cache->set($keyName, $this->columnNames);
                }
            } else {
                /**
                 * @var ServerSessionManager $serverSessionManager
                 */
                $serverSessionManager = $this->getService(ServerSessionManager::class);

                if (!$this->columnNames = $serverSessionManager->get($keyName)) {
                    $this->columnNames = $this->transportObject->loadColumnNames();
                    $serverSessionManager->set($keyName, $this->columnNames);
                }
            }
        }

        return $this->columnNames;
    }

    private function __construct($resourceName, $container)
    {
        $this->container = $container;
        $this->resourceName = $resourceName;
        if (!class_exists('pdoTransport')) {
            $pathsManager = $this->getService(PathsManager::class);
            $path = $pathsManager->getIncludeFilePath('modules/transportObjects/pdoTransport.class.php');
            include_once($path);
        }
        $this->transportObject = pdoTransport::getInstance($this->getService(ConfigManager::class)->getConfig('transport'));
        $this->transportObject->setResourceName($this->resourceName);
    }

    /**
     * @param array $searchFields
     * @param array $orderFields
     * @param bool $indexed
     * @param array $limitParameters
     * @return persistableObject[]
     */
    public function load($searchFields = [], $orderFields = [], $indexed = false, $limitParameters = [])
    {
        $this->transportObject->setResourceName($this->resourceName);

        $this->transportObject->setReturnColumns();
        $this->transportObject->setSearchLines($searchFields);
        $this->transportObject->setOrderFields($orderFields);
        $this->transportObject->setLimitFields($limitParameters);
        $this->transportObject->setGroupFields([]);

        if ($loadArray = $this->transportObject->selectData()) {
            return $this->makeObjectsFromData($loadArray, $indexed);
        }
        return [];
    }

    /**
     * @param array $searchFields
     * @param array $orderFields
     * @param bool $indexed
     * @param array $limitParameters
     * @return persistableObject[]
     */
    public function loadNew($searchFields = [], $orderFields = [], $indexed = false, $limitParameters = [])
    {
        /**
         * @var \Illuminate\Database\Query\Builder $query
         */
        $query = $this->getService('db')->table($this->resourceName);
        foreach ($searchFields as $data) {
            if ($data[1] == 'in') {
                $query->whereIn($data[0], $data[2]);
            } elseif ($data[1] == 'not in') {
                $query->whereNotIn($data[0], $data[2]);
            } else {
                $query->where($data[0], $data[1], $data[2]);
            }
        }
        foreach ($orderFields as $column => $value) {
            $query->orderBy($column, $value);
        }
        if ($limitParameters) {
            $query->offset($limitParameters[0]);
            $query->limit($limitParameters[1]);
        }

        if ($loadArray = $query->get()) {
            return $this->makeObjectsFromData($loadArray, $indexed);
        }
        return [];
    }

    protected function makeObjectsFromData($loadArray, $indexed = false)
    {
        $loadedObjects = [];
        if (!$indexed) {
            foreach ($loadArray as &$loadArrayItem) {
                $persistableObject = $this->getEmptyObject();
                $persistableObject->setData($loadArrayItem);
                $persistableObject->loaded = true;
                $persistableObject->persisted = true;

                $loadedObjects[] = $persistableObject;
            }
        } else {
            foreach ($loadArray as &$loadArrayItem) {
                $persistableObject = $this->getEmptyObject();
                $persistableObject->setData($loadArrayItem);
                $persistableObject->loaded = true;
                $persistableObject->persisted = true;

                $loadedObjects[$persistableObject->$indexed] = $persistableObject;
            }
        }
        return $loadedObjects;
    }

    public function getPersistableObject($fields)
    {
        if (!is_array($fields)) {
            $primaryFields = $this->getPrimaryFields();
            if (count($primaryFields) == 1) {
                $fields = [reset($primaryFields) => $fields];
            } else {
                $this->logError('Trying to get persistable object without index values');
            }
        }

        $result = $this->loadObject($fields);

        return $result;
    }

    public function loadObject($fields, $strict = true)
    {
        if ($objects = $this->load($fields, [], false, 1)) {
            if (count($objects) == 1 || !$strict) {
                return reset($objects);
            }
        }
        return false;
    }

    public function getEmptyObject()
    {
        $persistableObject = new persistableObject($this->resourceName);
        return $persistableObject;
    }

    public function deleteObject($object)
    {
        $objectData = $object->getData();

        $data = [];
        foreach ($this->getPrimaryFields() as $field) {
            $data[$field] = $objectData[$field];
        }

        $this->transportObject->setResourceName($this->resourceName);
        $this->transportObject->setSearchLines($data);
        $this->transportObject->deleteData();
    }

    /**
     * @param persistableObject $object
     */
    public function persistObject($object)
    {
        $data = $object->getData();
        $searchData = [];
        $idField = null;

        $persistedData = [];
        foreach ($this->getColumnNames() as $column) {
            if (isset($data[$column])) {
                $persistedData[$column] = $data[$column];
            }
        }

        $this->transportObject->setResourceName($this->resourceName);
        $primaryFields = $this->getPrimaryFields();
        foreach ($primaryFields as $field) {
            if (!isset($persistedData[$field])) {
                $idField = $field;
            }
        }
        if ($idField === null) {
            $idField = reset($primaryFields);
        }

        if (!$object->loaded) {
            $this->transportObject->setDataLines($persistedData);
            $this->transportObject->insertData();

            if (is_null($object->$idField)) {
                $newId = $this->transportObject->lastInsertedID;
                $object->$idField = $newId;
            }
        } else {
            $this->transportObject->setDataLines($persistedData);

            foreach ($primaryFields as $field) {
                $searchData[$field] = $data[$field];
            }

            $this->transportObject->setSearchLines($searchData);
            $this->transportObject->updateData();
        }
    }

    public function conditionalLoadObjects(
        $conditions,
        $orderFields = [],
        $limitFields = [],
        $groupFields = [],
        $literal = false,
    )
    {
        $this->transportObject->setResourceName($this->resourceName);
        $this->transportObject->setReturnColumns(null, $literal);
        $this->transportObject->setConditions($conditions);
        $this->transportObject->setOrderFields($orderFields);
        $this->transportObject->setLimitFields($limitFields);
        $this->transportObject->setGroupFields($groupFields);

        if ($loadArray = $this->transportObject->selectData()) {
            return $this->makeObjectsFromData($loadArray);
        }
        return [];
    }

    /**
     * @param $returnColumns
     * @param $conditions
     * @param array $orderFields
     * @param array $limitFields
     * @param array $groupFields
     * @param bool $literal
     * @return bool|array
     */
    public function conditionalLoad(
        $returnColumns,
        $conditions,
        $orderFields = [],
        $limitFields = [],
        $groupFields = [],
        $literal = false,
    )
    {
        $this->transportObject->setResourceName($this->resourceName);
        $this->transportObject->setReturnColumns($returnColumns, $literal);
        $this->transportObject->setConditions($conditions);
        $this->transportObject->setOrderFields($orderFields);
        $this->transportObject->setLimitFields($limitFields);
        $this->transportObject->setGroupFields($groupFields);
        $result = $this->transportObject->selectData();
        return $result;
    }

    public function conditionalOrLoad(
        $returnColumns,
        $conditions,
        $orderFields = [],
        $limitFields = [],
        $groupFields = [],
        $literal = false,
    )
    {
        $this->transportObject->setResourceName($this->resourceName);
        $this->transportObject->setReturnColumns($returnColumns, $literal);
        $this->transportObject->setOrConditions($conditions);
        $this->transportObject->setOrderFields($orderFields);
        $this->transportObject->setLimitFields($limitFields);
        $this->transportObject->setGroupFields($groupFields);
        $result = $this->transportObject->selectData();
        return $result;
    }

    /**
     * @param $column
     * @param null $conditions
     * @param array $group
     * @param bool $distinct
     * @return array|mixed
     *
     * @deprecated
     */
    public function countElements($column, $conditions = null, $group = [], $distinct = false)
    {
        $this->logError("Deprecated method used: countElements");

        if (!$distinct) {
            $name = 'count(`' . $column . '`)';
        } else {
            $name = 'count(distinct(`' . $column . '`))';
        }
        $this->transportObject->setResourceName($this->resourceName);
        $this->transportObject->setReturnColumns([$name], true);
        $this->transportObject->setConditions($conditions);
        $this->transportObject->setOrderFields([]);
        $this->transportObject->setLimitFields([]);
        $this->transportObject->setGroupFields($group);
        $result = $this->transportObject->selectData();
        if (is_array($result) && count($result)) {
            $result = reset($result);
            if (isset($result[$name])) {
                $result = $result[$name];
            }
        }
        return $result;
    }

    public function averageValue($column, $conditions = [], $orderFields = [], $limitFields = [])
    {
        $name = 'AVG(`' . $column . '`)';

        $this->transportObject->setResourceName($this->resourceName);
        $this->transportObject->setReturnColumns([$name], true);
        $this->transportObject->setConditions($conditions);
        $this->transportObject->setOrderFields($orderFields);
        $this->transportObject->setLimitFields($limitFields);
        $result = $this->transportObject->selectData();
        if (is_array($result) && count($result)) {
            $result = reset($result);
            if (isset($result[$name])) {
                $result = $result[$name];
            }
        }
        return $result;
    }

    public function updateData($data, $conditions)
    {
        $this->transportObject->setResourceName($this->resourceName);
        $this->transportObject->setDataLines($data);
        $this->transportObject->setSearchLines($conditions);
        return $this->transportObject->updateData();
    }

    public function directQuery($query)
    {
        $result = $this->transportObject->sendDirectQuery($query);
        return $result;
    }
}


