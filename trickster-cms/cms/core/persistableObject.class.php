<?php

/**
 * Class persistableObject
 */
class persistableObject
{
    /**
     * array of loaded data from database row
     * @var array
     */
    protected $rowData = [];
    /**
     * Shows whether this object was loaded from a database or it's created dynamically
     * @var bool
     */
    public $loaded = false;
    /**
     * Shows if this object was not modified after being loaded from a database
     * @var bool
     */
    public $persisted = false;
    /**
     * Collection resource name (database table name).
     * Only used to store table name during serialization, normally should be null
     * @var string
     */
    protected $resourceName;

    /**
     * @param string $resourceName
     */
    public function __construct($resourceName)
    {
        $this->resourceName = $resourceName;
    }

    /**
     * magic method for getting properties straight from $rowData
     *
     * @param string $variable
     * @return null|string
     */
    public function __get($variable)
    {
        if (!$this->loaded && $this->persisted == true) {
            $this->load();
        }
        if (isset($this->rowData[$variable])) {
            return $this->rowData[$variable];
        } else {
            return null;
        }
    }

    /**
     * magic method to set value for some variable (index) in $rowData
     *
     * @param string $variable
     * @param mixed $value
     */
    public function __set($variable, $value)
    {
        $this->rowData[$variable] = $value;
        if ($this->persisted) {
            $this->persisted = false;
        }
    }

    /**
     * return all row data as array
     *
     * @return string[]
     */
    public function getData()
    {
        return $this->rowData;
    }

    /**
     * directly set the row data as an array
     *
     * @param array $data
     */
    public function setData($data)
    {
        $this->rowData = $data;
    }

    /**
     * Deletes this object from collection (database)
     */
    public function delete()
    {
        persistableCollection::getInstance($this->resourceName)->deleteObject($this);
    }

    /**
     * Inserts or updates object into collection (database)
     */
    public function persist()
    {
        persistableCollection::getInstance($this->resourceName)->persistObject($this);
        $this->loaded = true;
        $this->persisted = true;
    }

    /**
     * Loads data according to primary fields value if it wasn't loaded before
     *
     * @return $this
     */
    public function load()
    {
        if (!$this->loaded) {
            $searchData = [];
            foreach (persistableCollection::getInstance($this->resourceName)->getPrimaryFields() as $fieldName) {
                $searchData[$fieldName] = $this->$fieldName;
            }
            $result = persistableCollection::getInstance($this->resourceName)->loadObject($searchData);
            $this->persisted = true;
            $this->loaded = true;
            return $result;
        }
        return $this;
    }

    /**
     * Magic method to prepare object for serialization.
     * Ignores link to collection which will be restored after deserialization
     * @return array
     */
    public function __sleep()
    {
        return ['rowData', 'loaded', 'persisted', 'resourceName'];
    }

    /**
     * Magic method to re-init object after deserialization.
     * Restores collection instance using stored resource name
     */
    public function __wakeup()
    {
    }

    public static function __set_state($state)
    {
        $result = new persistableObject($state['resourceName']);
        $result->rowData = $state['rowData'];
        $result->loaded = $state['loaded'];
        $result->persisted = $state['persisted'];
        return $result;
    }
}

