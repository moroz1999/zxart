<?php

use App\Paths\PathsManager;
use App\Structure\ActionFactory;

/**
 * @property string|int $id
 * @property string $title
 * @property string $structureType
 * @property string $structureRole
 * @property string $structureName
 * @property string $dateCreated
 * @property string $dateModified
 * @property string $marker
 */
abstract class structureElement implements DependencyInjectionContextInterface
{
    use DependencyInjectionContextTrait;
    use TabsTrait;

    /**
     * @var persistableObject
     */
    protected $structureDataObject;
    protected $actionPerformed = false;
    protected $childrenLoadStatus;
    protected $allowedTypes;
    protected $allowedTypesByAction = [];
    protected $multiLanguageChunks = [];
    protected $singleLanguageChunks = [];
    protected $structureFields = [];
    protected $moduleFields = [];
    protected $multiLanguageFields = null;
    public $languagesParentElementMarker = '';
    public $defaultActionName = 'showElement';
    public $actionName = '';
    protected $viewName = 'list';
    protected $template;
    public $dataResourceName;
    protected $contentList;
    public $childrenList = [];
    protected $moduleDataObjects = [];
    public $level;
    public $structurePath;
    public $requested = false;
    public $final = false;
    /**
     * @var string|null
     */
    public $URL;
    public $role;
    protected $currentLanguage;
    protected $formData;
    protected $formNames;
    protected $formErrors;
    protected $languageElement;
    protected $currentParentElementId;
    protected $currentParentElement;
    protected $replaceMissingLanguageData = true;

    public bool $newlyCreated = false;
    public $privilegesForm;
    public $navigated;
    public $positionsForm;

    public function __construct($rootMarkerPublic)
    {
        $this->languagesParentElementMarker = $this->languagesParentElementMarker ?: $rootMarkerPublic;
        $this->childrenLoadStatus = [
            'content' => [],
            'container' => [],
        ];

        $this->structureFields = [
            'id' => 'structure',
            'structureType' => 'structure',
            'structureName' => 'structureName',
            'structureRole' => 'structure',
            'dateCreated' => 'dateTime',
            'dateModified' => 'dateTime',
            'marker' => 'text',
        ];
        $this->setModuleStructure($this->moduleFields);
        $this->initialize();
    }

    protected function initialize()
    {
        return true;
    }

    /**
     * Checks whether this element is temporary or has been saved to db and has actual id
     *
     * @return bool
     */
    public function hasActualStructureInfo()
    {
        return $this->structureDataObject->loaded;
    }

    /**
     * Used to check whether all children elements of particular role and link type have been loaded from db
     *
     * @param string|string[] $types - link type (structure, connected etc)
     * @param string $role - element's role (content/container)
     * @return bool
     */
    public function getChildrenLoadedStatus($types, $role)
    {
        if (is_array($types)) {
            foreach ($types as $type) {
                if (!isset($this->childrenLoadStatus[$role][$type])) {
                    return false;
                }
            }
            return true;
        }

        return $this->childrenLoadStatus[$role][$types ?? ''] ?? false;
    }

    /**
     * Used to mark that all children elements of particular role and link type have been loaded from db.
     * This info is used to prevent the same DB queries to be sent again
     *
     * @param string|string[] $types - link type (structure, connected etc)
     * @param string $role - element's role (content/container)
     * @param bool $value
     */
    public function setChildrenLoadedStatus($types, $role, $value)
    {
        if (is_array($types)) {
            foreach ($types as $type) {
                $this->childrenLoadStatus[$role][$type] = $value;
            }
        } else {
            $this->childrenLoadStatus[$role][$types] = $value;
        }
    }

    /**
     * dataChunk object manufacturing method
     *
     * @param string $type - datachunk type
     * @param string $fieldName - database field name
     * @return DataChunk|bool
     */
    protected function manufactureChunkObject($type, $fieldName)
    {
        if (is_array($type)) {
            $properties = $type[1];
            $type = $type[0];
        }

        $className = $type . 'DataChunk';

        if (class_exists($className, true)) {
            /**
             * @var DataChunk $chunk
             */
            $chunk = new $className($fieldName);
            $this->instantiateContext($chunk);
            if ($chunk instanceof ElementHolderInterface) {
                $chunk->setStructureElement($this);
            }
            if (isset($properties)) {
                $chunk->setProperties($properties);
            }
            return $chunk;
        } else {
            $this->logError('Datachunk class ' . $className . ' is missing');
            return false;
        }
    }

    /**
     * Creates action object and executes the action.
     *
     * @param string $customAction - custom action name. If provided, overrides the default action
     * @return bool - returns false if action has been executed before
     */
    public function executeAction($customAction = null)
    {
        if (!$this->actionPerformed || $customAction) {
            if ($customAction) {
                $actionName = $customAction;
            } else {
                $actionName = $this->actionName;
            }
            /**
             * @var ActionFactory $actionFactory
             */
            $actionFactory = $this->getService(ActionFactory::class);

            $actionObject = $actionFactory->makeActionObject($this->structureType, $actionName);
            $actionObject->structureElement = $this;
            if ($actionObject instanceof DependencyInjectionContextInterface) {
                $this->instantiateContext($actionObject);
            }

            /**
             * we need to recheck privileges here if custom action has been called.
             *
             * @var structureManager $structureManager
             */
            $structureManager = $this->getService('structureManager');
            if (!$structureManager->checkPrivileges(
                $this->id,
                $actionObject->actionName,
                $this->structureType
            )) {
                return false;
            }

            $this->actionPerformed = true;
            $actionObject->startAction();

            return true;
        }
        return false;
    }

    /**
     * completely deletes the element from database and all of it's children
     */
    public function deleteElementData()
    {
        /**
         * @var linksManager $linksManager
         */
        $linksManager = $this->getService(linksManager::class);
        /**
         * @var structureManager $structureManager
         */
        $structureManager = $this->getService('structureManager');

        if (method_exists($this, 'getDeletionLinkTypes')) {
            $types = $this->getDeletionLinkTypes();
        } else {
            $types = ['structure'];
        }

        if ($childrenList = $structureManager->getElementsChildren($this->id, null, $types)) {
            foreach ($childrenList as $childElement) {
                $childElement->deleteElementData();
            }
        }
        if ($linksObjects = $linksManager->getElementsLinks($this->id, false)) {
            foreach ($linksObjects as $linkObject) {
                $linkObject->delete();
                if ($linkObject->childStructureId == $this->id) {
                    $structureManager->clearElementCache($linkObject->parentStructureId);
                }
            }
        }
        foreach ($this->getAllDataChunks() as $chunks) {
            foreach ($chunks as $dataChunk) {
                if ($dataChunk instanceof ExtraDataHolderDataChunkInterface) {
                    $dataChunk->deleteExtraData();
                }
            }
        }
        if ($moduleDataObjects = $this->getModuleDataObjects()) {
            foreach ($moduleDataObjects as $dataObject) {
                $dataObject->delete();
            }
        }
        $this->structureDataObject->delete();
        $structureManager->clearElementCache($this->id);
    }

    /**
     * Save all structure data into database and update id with generated information
     * Also generates a date of element creation and a date of last update
     */
    protected function persistStructureData()
    {
        $oldId = $this->id;

        if (!$this->structureRole) {
            $this->structureRole = $this->role;
        }

        $nowDate = time();
        if (!$this->hasActualStructureInfo()) {
            $this->id = null;
            $this->dateCreated = $nowDate;
        }

        $this->dateModified = $nowDate;

        $this->structureDataObject->persist();

        //update id in data chunk object with new generated value from a database
        $this->id = (int)$this->structureDataObject->id;

        if ($oldId != $this->id) {
            $structureManager = $this->getService('structureManager');
            $structureManager->reRegisterElement($oldId, $this->id);

            $controller = $this->getService(controller::class);
            $controller->reRegisterElement($oldId, $this->id);
        }
    }

    protected function persistModuleData()
    {
        if ($moduleDataObjects = $this->getModuleDataObjects()) {
            foreach ($moduleDataObjects as $moduleDataObject) {
                if (!$moduleDataObject->loaded) {
                    //this object is newly created and misses some values.

                    //set the same id value as in structure table
                    $moduleDataObject->id = $this->structureDataObject->id;

                    //synchronize its single-language values with earlier saved values to avoid data losses
                    foreach ($this->moduleFields as $fieldName => $type) {
                        if (!isset($this->multiLanguageFields[$fieldName])) {
                            if ($chunk = $this->getDataChunk($fieldName)) {
                                $moduleDataObject->$fieldName = $chunk->getStorageValue();
                            }
                        }
                    }
                }

                $moduleDataObject->persist();
            }
        }
    }

    public function persistElementData()
    {
        $structureManager = $this->getService('structureManager');

        //save structure object to update dateModified and ensure that element has numeric ID already
        $this->persistStructureData();

        //now check structure name
        if (($newName = $structureManager->checkStructureName($this)) !== $this->structureName) {
            $this->structureName = $newName;
            $this->structureDataObject->persist();
        }

        //now save module table records
        $this->persistModuleData();

        $this->persistStructureLinks();

        foreach ($this->getAllDataChunks() as $languageChunks) {
            foreach ($languageChunks as $dataChunk) {
                if ($dataChunk instanceof ExtraDataHolderDataChunkInterface) {
                    $dataChunk->persistExtraData();
                }
            }
        }

        //we cannot check structure name before first persisting into database.
        $structureName = $structureManager->checkStructureName($this);
        if (!$this->structureName || $this->structureName != $structureName) {
            $this->structureName = $structureName;
            $this->persistStructureData();
        }
        $structureManager->regenerateStructureInfo($this);
        $structureManager->clearElementCache($this->id);
    }

    public function persistStructureLinks()
    {
        $structureManager = $this->getService('structureManager');
        $linksManager = $this->getService(linksManager::class);
        $linksObjects = $linksManager->getElementsLinks($this->id, null, 'child');

        foreach ($linksObjects as $linkObject) {
            $linkObject->childStructureId = $this->id;
            $linkObject->persist();
            $structureManager->clearElementCache($linkObject->parentStructureId);
        }
        $linksManager->resetElementsCacheById($this->id);
    }

    /**
     * remove the method usages and use getId instead of property
     *
     * @deprecated
     */
    public function prepareActualData()
    {
        //no need to uncomment this method until we make a global refactoring
        //        $this->logError('deprecated method prepareActualData used');
        if (!$this->hasActualStructureInfo()) {
            $this->persistStructureData();
        }
    }

    /**
     * Automatically persists element and returns numeric id of structure manager
     */
    public function getPersistedId(): int
    {
        if (!$this->hasActualStructureInfo()) {
            $this->persistStructureData();
        }
        return (int)$this->id;
    }

    public function getId(): int
    {
        return (int)$this->id;
    }

    /**
     * Generates form data, field names and errors for current structure element
     */
    protected function prepareFormData()
    {
        $formData = [];
        $formNames = [];
        $formErrors = [];

        foreach ($this->getAllDataChunks() as $languageId => $languageChunks) {
            foreach ($languageChunks as $fieldName => $chunk) {
                if (isset($this->multiLanguageFields[$fieldName])) {
                    $formData[$fieldName][$languageId] = $chunk->getFormValue();
                    $formNames[$fieldName][$languageId] = $this->getFormNamesBase() . '[' . $languageId . '][' . $fieldName . ']';
                    $formErrors[$fieldName][$languageId] = &$chunk->formError;
                } else {
                    $formData[$fieldName] = $chunk->getFormValue();
                    $formNames[$fieldName] = $this->getFormNamesBase() . '[' . $fieldName . ']';
                    $formErrors[$fieldName] = &$chunk->formError;
                }
            }
        }

        $this->formData = $formData;
        $this->formNames = $formNames;
        $this->formErrors = $formErrors;
    }

    /**
     * sets externally loaded structure data object
     *
     * @param persistableObject $object
     */
    public function setStructureDataObject($object)
    {
        $this->structureDataObject = $object;
    }

    public function setModuleDataObject($object, $languageId)
    {
        $this->moduleDataObjects[$languageId] = $object;
    }

    public function createEmptyModuleObjects()
    {
        if ($languages = $this->getLanguagesList()) {
            foreach ($languages as $languageId) {
                $this->moduleDataObjects[$languageId] = $this->createEmptyModuleObject($languageId);
            }
        }
    }

    protected function createEmptyModuleObject($languageId)
    {
        $collection = persistableCollection::getInstance($this->dataResourceName);
        $dataObject = $collection->getEmptyObject();
        $dataObject->languageId = $languageId;

        return $dataObject;
    }

    /**
     * gets current language value for this element
     *
     * @return int - if 0, then element is not multilingual
     */
    public function getCurrentLanguage()
    {
        if ($this->currentLanguage === null) {
            if ($this->getMultiLanguageFields()) {
                $this->currentLanguage = $this->getService(LanguagesManager::class)
                    ->getCurrentLanguageId($this->languagesParentElementMarker);
            } else {
                $this->currentLanguage = 0;
            }
        }
        return $this->currentLanguage;
    }

    /**
     * returns element's languages id list. If non-multilanguage element, returns array(0)
     *
     * @return array
     */
    public function getLanguagesList()
    {
        if ($this->getMultiLanguageFields()) {
            $languagesManager = $this->getService(LanguagesManager::class);
            $languages = $languagesManager->getLanguagesIdList($this->languagesParentElementMarker);
        } else {
            $languages = ['0'];
        }
        return $languages;
    }

    /**
     * Current language setter for this element
     * Current language is a language id of default module data object for multilingual elements
     *
     * @param int $id
     */
    public function setCurrentLanguage($id)
    {
        $this->currentLanguage = $id;
    }

    /**
     * Error log entry generation. Also provides classname as a location
     *
     * @param $errorText
     * @throws Exception
     */
    protected function logError($errorText)
    {
        $locationName = 'Structure element: ' . get_class($this) . ' ' . $this->id;
        $errorLogObject = ErrorLog::getInstance();
        $errorLogObject->logMessage($locationName, $errorText);
    }

    public function getMultiLanguageFields()
    {
        if ($this->multiLanguageFields === null) {
            if (method_exists($this, 'setMultiLanguageFields')) {
                $this->setMultiLanguageFields($this->multiLanguageFields);
                if (!empty($this->multiLanguageFields)) {
                    $this->multiLanguageFields = array_flip($this->multiLanguageFields);
                }
            } else {
                $this->multiLanguageFields = [];
            }
        }
        return $this->multiLanguageFields;
    }

    protected function isFieldMultiLanguage($dataChunk)
    {
        if ($multiLanguageFields = $this->getMultiLanguageFields()) {
            return isset($multiLanguageFields[$dataChunk]);
        }
        return false;
    }


    /**
     * Load and get module data object for the provided language
     *
     * @param int $languageId
     * @return persistableObject
     */
    protected function getModuleDataObject($languageId = null)
    {
        if ($languageId === null) {
            $languageId = $this->getCurrentLanguage();
        }
        if (!isset($this->moduleDataObjects[$languageId])) {
            $collection = persistableCollection::getInstance($this->dataResourceName);
            //sending no language id works faster, so we skip it if it's not multilanguage
            if ($languageId == 0) {
                $conditions = [
                    'id' => $this->id,
                ];
            } else {
                $conditions = [
                    'id' => $this->id,
                    'languageId' => $languageId,
                ];
            }

            //try to load language data for provided language
            if ($moduleObject = $collection->loadObject($conditions)
            ) {
                $this->moduleDataObjects[$languageId] = $moduleObject;
            } else {
                $this->moduleDataObjects[$languageId] = $this->createEmptyModuleObject($languageId);

                if ($this->replaceMissingLanguageData && $moduleObjects = $this->getModuleDataObjects()) {
                    foreach ($moduleObjects as $moduleDataObject) {
                        if ($moduleDataObject->loaded) {
                            $this->moduleDataObjects[$languageId]->setData($moduleDataObject->getData());
                            $this->moduleDataObjects[$languageId]->languageId = $languageId;
                            break;
                        }
                    }
                }
            }
        }

        return $this->moduleDataObjects[$languageId];
    }

    /**
     * Get all module data objects for all languages
     *
     * @return persistableObject[]
     */
    protected function getModuleDataObjects()
    {
        $moduleDataObjects = [];
        if ($this->getMultiLanguageFields()) {
            $languagesManager = $this->getService(LanguagesManager::class);
            if ($languages = $languagesManager->getLanguagesIdList($this->languagesParentElementMarker)) {
                foreach ($languages as $languageId) {
                    if ($moduleDataObject = $this->getModuleDataObject($languageId)) {
                        $moduleDataObjects[$languageId] = $moduleDataObject;
                    }
                }
            }
        } else {
            if ($moduleDataObject = $this->getModuleDataObject($this->getCurrentLanguage())) {
                $moduleDataObjects[$this->getCurrentLanguage()] = $moduleDataObject;
            }
        }
        return $moduleDataObjects;
    }

    public function importExternalData(
        $externalData,
        $expectedFields = [],
        $validators = [],
        $filteredLanguageId = false,
    )
    {
        if (!$expectedFields) {
            $expectedFields = array_keys($externalData);
        }

        $languagesManager = $this->getService(LanguagesManager::class);
        $languages = $languagesManager->getLanguagesIdList($this->languagesParentElementMarker);
        $validated = true;

        foreach ($languages as $languageId) {
            if ($filteredLanguageId === false || $filteredLanguageId == $languageId) {
                foreach ($expectedFields as $dataChunkName) {
                    if ($this->isFieldMultiLanguage($dataChunkName)) {
                        if ($dataChunk = $this->getDataChunk($dataChunkName, $languageId)) {
                            if (!isset($externalData[$languageId][$dataChunkName])) {
                                $externalData[$languageId][$dataChunkName] = null;
                            }
                            $dataChunk->setFormValue($externalData[$languageId][$dataChunkName]);

                            if (isset($validators[$dataChunkName])) {
                                if (!$dataChunk->validateFormData($validators[$dataChunkName])) {
                                    $validated = false;
                                }
                            }
                        } else {
                            $this->logError("chunk " . $dataChunkName . " doesn't exist");
                        }
                    }
                }
            }
        }

        foreach ($expectedFields as $dataChunkName) {
            if (!$this->isFieldMultiLanguage($dataChunkName)) {
                if ($dataChunk = $this->getDataChunk($dataChunkName)) {
                    if (!isset($externalData[$dataChunkName])) {
                        $externalData[$dataChunkName] = null;
                    }
                    $dataChunk->setFormValue($externalData[$dataChunkName]);

                    if (isset($validators[$dataChunkName])) {
                        if (!$dataChunk->validateFormData($validators[$dataChunkName])) {
                            $validated = false;
                        }
                    }
                } else {
                    $this->logError("chunk " . $dataChunkName . " doesn't exist");
                }
            }
        }

        if ($validated) {
            foreach ($expectedFields as $dataChunkName) {
                if ($this->isFieldMultiLanguage($dataChunkName)) {
                    foreach ($languages as $languageId) {
                        if ($filteredLanguageId === false || $filteredLanguageId == $languageId) {
                            if ($dataChunk = $this->getDataChunk($dataChunkName, $languageId)) {
                                $dataChunk->convertFormToStorage();
                                if ($dataChunk instanceof ElementStorageValueHolderInterface) {
                                    if (isset($this->moduleFields[$dataChunkName])) {
                                        if ($moduleDataObject = $this->getModuleDataObject($languageId)) {
                                            $moduleDataObject->$dataChunkName = $dataChunk->getElementStorageValue();
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                    if ($dataChunk = $this->getDataChunk($dataChunkName)) {
                        $dataChunk->convertFormToStorage();
                        if ($dataChunk instanceof ElementStorageValueHolderInterface) {
                            if (isset($this->moduleFields[$dataChunkName])) {
                                if ($moduleDataObjects = $this->getModuleDataObjects()) {
                                    foreach ($moduleDataObjects as $moduleDataObject) {
                                        $moduleDataObject->$dataChunkName = $dataChunk->getElementStorageValue();
                                    }
                                }
                            } else {
                                if (isset($this->structureFields[$dataChunkName])) {
                                    $this->structureDataObject->$dataChunkName = $dataChunk->getElementStorageValue();
                                }
                            }
                        }
                    } else {
                        $this->logError('Expected chunk object "' . $dataChunkName . '" is not found');
                    }
                }
            }
        }
        return $validated;
    }

    /**
     * Magic get method to get display value from dataChunk
     * Display value is automatically converted from storage value, which is automatically
     * loaded from database via appropriate structure or module data object
     *
     * @param string $propertyName
     * @return null|mixed
     */
    public function __get($propertyName)
    {
        if ($chunkObject = $this->getDataChunk($propertyName)) {
            return $chunkObject->getDisplayValue();
        } else {
            $this->$propertyName = null;
            return null;
        }
    }

    public function __isset($propertyName)
    {
        if ($chunkObject = $this->getDataChunk($propertyName)) {
            return true;
        }
        return false;
    }

    /**
     * Magic set method to set external value to dataChunk according to property name.
     * External value is being converted to storage value which automatically goes to appropriate
     * storage/module data object
     *
     * @param string $propertyName
     * @param mixed $value
     */
    public function __set($propertyName, $value)
    {
        if ($dataChunk = $this->getDataChunk($propertyName)) {
            $dataChunk->setExternalValue($value);
            $storageValue = $dataChunk->getStorageValue();

            //direct setting affects all languages
            if ($dataObjects = $this->getDataObjectsForProperty($propertyName)) {
                foreach ($dataObjects as $dataObject) {
                    $dataObject->$propertyName = $storageValue;
                }
            }
        } else {
            if (property_exists($this, $propertyName)) {
                $this->$propertyName = $value;
            }
        }
    }

    public function addModuleFields($moduleFields)
    {
        if (count($moduleFields)) {
            foreach ($moduleFields as $fieldName => $fieldInfo) {
                $this->moduleFields[$fieldName] = $fieldInfo;
            }
        }
    }

    public function getStructureData()
    {
        $structureData = [];
        foreach ($this->structureFields as $fieldName => $type) {
            if ($fieldName != 'id') {
                $structureData[$fieldName] = $this->structureDataObject->$fieldName;
            }
        }
        return $structureData;
    }

    public function getModuleData()
    {
        $moduleData = [];
        if ($moduleDataObjects = $this->getModuleDataObjects()) {
            foreach ($moduleDataObjects as $moduleDataObject) {
                $languageId = $moduleDataObject->languageId;
                $moduleData[$languageId] = [];
                foreach ($this->moduleFields as $fieldName => $type) {
                    $moduleData[$languageId][$fieldName] = $moduleDataObject->$fieldName;
                }
            }
        }
        return $moduleData;
    }

    public function getExportData()
    {
        $exportData = [];
        $exportData['structureData'] = $this->getStructureData();
        $exportData['moduleData'] = $this->getModuleData();
        $structureManager = $this->getService('structureManager');
        $childrenList = $structureManager->getElementsChildren($this->id);
        $exportData['childrenData'] = [];
        foreach ($childrenList as $element) {
            $exportData['childrenData'][] = $element->getExportData();
        }

        return $exportData;
    }

    //todo: analyse and refactor
    public function importExportedData($structureData, $moduleData)
    {
        $changed = false;
        foreach ($structureData as $fieldName => $value) {
            if ($this->structureDataObject->$fieldName != $value) {
                $this->structureDataObject->$fieldName = $value;
                $changed = true;
            }
        }
        if (isset($structureData['structureName'])) {
            $this->structureName = $structureData['structureName'];
        }

        // lang codes to ids
        $languageIds = [];
        $groupName = (in_array($structureData['structureType'], [
            'adminTranslationsGroup',
            'adminTranslation',
        ])) ? 'adminLanguages' : $this->getService(ConfigManager::class)->get('main.rootMarkerPublic');
        $languagesManager = $this->getService(LanguagesManager::class);
        $languagesList = $languagesManager->getLanguagesList($groupName);
        foreach ($languagesList as $languagesItem) {
            $languageIds[$languagesItem->iso6393] = $languagesItem->id;
        }

        foreach ($moduleData as $languageCode => $data) {
            $languageId = $languageCode ? $languageIds[$languageCode] : null;
            if ($moduleDataObject = $this->getModuleDataObject($languageId)) {
                foreach ($data as $fieldName => $value) {
                    if ($value != '') {
                        if ($moduleDataObject->$fieldName != $value) {
                            $moduleDataObject->$fieldName = $value;
                            $changed = true;
                        }
                    }
                }
            }
        }
        return $changed;
    }

    /**
     * Returns an array of data objects according to provided property name.
     * Used in setters
     *
     * @param string $propertyName
     * @return bool|persistableObject[]
     */
    protected function getDataObjectsForProperty($propertyName)
    {
        if (isset($this->structureFields[$propertyName])) {
            return [$this->structureDataObject];
        } else {
            if (isset($this->moduleFields[$propertyName])) {
                return $this->getModuleDataObjects();
            }
        }
        return false;
    }

    /**
     * Set value for a property from a form. Form values are not applied to structure element until they are all correct
     * Form values are like a temporary storage of user input until they pass the validation during action
     *
     * @param string $propertyName
     * @param mixed $value
     */
    public function setFormValue($propertyName, $value)
    {
        if (!isset($this->formData)) {
            $this->prepareFormData();
        }

        if ($chunkObject = $this->getDataChunk($propertyName)) {
            $chunkObject->setFormValue($value);
            $this->formData[$propertyName] = $chunkObject->getFormValue();
        }
    }

    /**
     * Get value for a property from a form.
     *
     * @param string $propertyName
     * @return bool|mixed
     */
    public function getFormValue($propertyName)
    {
        if (!isset($this->formData)) {
            $this->prepareFormData();
        }

        if ($chunkObject = $this->getDataChunk($propertyName)) {
            return $chunkObject->getFormValue();
        }
        return false;
    }

    /**
     * Manually set the validation error for any of the form fields
     *
     * @param string $propertyName
     * @param bool|string $value
     */
    public function setFormError($propertyName, $value = true)
    {
        if (!isset($this->formErrors)) {
            $this->prepareFormData();
        }

        if ($chunkObject = $this->getDataChunk($propertyName)) {
            $chunkObject->formError = true;
            $this->formErrors[$propertyName] = $value;
        }
    }

    /**
     * Returns languageId-indexed array of datachunk type-indexed arrays
     *
     * @return DataChunk[][]
     */
    public function getAllDataChunks()
    {
        $allChunks = [];
        if ($languages = $this->getLanguagesList()) {
            foreach ($languages as $languageId) {
                $languageChunks = [];
                foreach ($this->structureFields as $fieldName => $fieldType) {
                    $languageChunks[$fieldName] = $this->getDataChunk($fieldName, $languageId);
                }

                foreach ($this->moduleFields as $fieldName => $fieldType) {
                    $languageChunks[$fieldName] = $this->getDataChunk($fieldName, $languageId);
                }
                $allChunks[$languageId] = $languageChunks;
            }
        }
        return $allChunks;
    }

    /**
     * Returns indexed list of dataChunk objects for one multilanguage property
     *
     * @param $propertyName
     * @return dataChunk[]
     */
    public function getMultilanguageDataChunk($propertyName)
    {
        $result = [];
        foreach ($this->getLanguagesList() as $languageId) {
            $result[$languageId] = $this->getDataChunk($propertyName, $languageId);
        }
        return $result;
    }

    /**
     * Creates and returns appropriate dataChunk according to propertyName.
     *
     * @param string $propertyName
     * @param null|int $languageId
     */
    public function getDataChunk($propertyName, $languageId = null): ?DataChunk
    {
        if (isset($this->singleLanguageChunks[$propertyName])) {
            return $this->singleLanguageChunks[$propertyName];
        }
        if ($languageId === null) {
            $languageId = $this->getCurrentLanguage();
        }
        if (isset($this->multiLanguageChunks[$languageId][$propertyName])) {
            return $this->multiLanguageChunks[$languageId][$propertyName];
        }

        if (isset($this->structureFields[$propertyName])) {
            if ($chunkObject = $this->manufactureChunkObject($this->structureFields[$propertyName], $propertyName)) {
                if ($chunkObject instanceof ElementStorageValueHolderInterface) {
                    $chunkObject->setElementStorageValue($this->structureDataObject->$propertyName);
                }
                $this->singleLanguageChunks[$propertyName] = $chunkObject;
                return $chunkObject;
            }
        } elseif (isset($this->moduleFields[$propertyName]) && ($moduleDataObject = $this->getModuleDataObject($languageId)) && $chunkObject = $this->manufactureChunkObject($this->moduleFields[$propertyName], $propertyName)
        ) {
            if ($chunkObject instanceof ElementStorageValueHolderInterface) {
                //some data chunks do not exist in database directly.
                $chunkObject->setElementStorageValue($moduleDataObject->$propertyName);
            }
            if ($this->isFieldMultiLanguage($propertyName)) {
                $this->multiLanguageChunks[$languageId][$propertyName] = $chunkObject;
            } else {
                $this->singleLanguageChunks[$propertyName] = $chunkObject;
            }

            return $chunkObject;
        }

        return null;
    }

    /**
     * formData is a array of HTML form values
     *
     * @return array - index by property name
     */
    public function getFormData()
    {
        if ($this->formData === null) {
            $this->prepareFormData();
        }

        return $this->formData;
    }

    /**
     * If there was any validation error during action execution, formErrors array is populated with validation
     * errors information
     *
     * @return array - index by property name
     */
    public function getFormErrors()
    {
        if ($this->formErrors === null) {
            $this->prepareFormData();
        }
        return $this->formErrors;
    }

    /**
     * Form names are html form field names, which are generated according to their type and element id-number
     *
     * @return string[] - indexed by property name.
     */
    public function getFormNames()
    {
        if ($this->formNames === null) {
            $this->prepareFormData();
        }
        return $this->formNames;
    }

    /**
     * Returns a first part of form names. Can be used to provide this info for Javascript, for example
     *
     * @return string
     */
    public function getFormNamesBase()
    {
        return 'formData[' . $this->id . ']';
    }

    public function setValue($propertyName, $value, $languageId = 0)
    {
        if ($dataChunk = $this->getDataChunk($propertyName, $languageId)) {
            $dataChunk->setExternalValue($value);
            $storageValue = $dataChunk->getStorageValue();

            $dataObjects = $this->getDataObjectsForProperty($propertyName);

            foreach ($dataObjects as $dataObject) {
                if ($dataObject->languageId == $languageId) {
                    $dataObject->$propertyName = $storageValue;
                }
            }
        } else {
            $this->$propertyName = $value;
        }
    }

    public function getValue($propertyName, $languageId = 0)
    {
        if ($dataChunk = $this->getDataChunk($propertyName, $languageId)) {
            return $dataChunk->getStorageValue();
        }

        return null;
    }

    public function getLanguageValue($propertyName, $languageId = 0)
    {
        if ($dataChunk = $this->getDataChunk($propertyName, $languageId)) {
            return $dataChunk->getStorageValue();
        }

        return false;
    }

    abstract protected function setModuleStructure(&$moduleStructure);

    /**
     * Returns the last set template filename or generates custom filename if custom viewName is provided
     *
     * @param string $viewName
     * @return string
     */
    public function getTemplate($viewName = null)
    {
        //if custom view has been provided, generate template filename using custom view name
        if ($viewName !== null) {
            return $this->structureType . '.' . $viewName . '.tpl';
        }
        //if no template has been assigned directly before, generate the default template filename using last set viewName
        if ($this->template === null) {
            $this->template = $this->structureType . '.' . $this->viewName . '.tpl';
        }
        return $this->template;
    }

    /**
     * Sets the direct filename for a used template
     *
     * @param string $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * Returns the current set viewName. viewName is used for a template filename generation.
     *
     * @return string
     */
    public function getViewName()
    {
        return $this->viewName;
    }

    /**
     * Setter for viewName parameter
     *
     * @param string $viewName
     */
    public function setViewName($viewName)
    {
        $this->viewName = $viewName;
    }

    /**
     * When element is used as a form, this method is used to return the URL for submission of a form
     *
     * @param string $type - can be used when overriden for additional data passing
     * @return string
     */
    public function getFormActionURL($type = null)
    {
        /**
         * @var $controller controller
         */
        $controller = $this->getService(controller::class);
        if ($linkType = $controller->getParameter('linkType')) {
            return $this->URL . 'linkType:' . $linkType . '/';
        }
        return $this->URL;
    }

    /**
     * deprecated!
     * todo: refactor this one.
     *
     *
     * return some kind of "content", shouldn't be used
     *
     * @return structureElement[]
     * @deprecated
     */
    public function getContentList()
    {
        //$this->logError("Deprecated method used: getContentList");

        if ($this->contentList === null) {
            $this->contentList = $this->getChildrenList();
        }
        return $this->contentList;
    }

    /**
     * Returns the children list of current element. Only for "structure" links at the moment
     *
     * @param string $roles
     * @param string $linkType
     * @param null $allowedTypes
     * @param bool $restrictLinkTypes
     * @return structureElement[]
     */
    public function getChildrenList(
        $roles = null,
        $linkType = 'structure',
        $allowedTypes = null,
        $restrictLinkTypes = false,
    )
    {
        $structureManager = $this->getService('structureManager');
        //is it possible that we should always use blacklist when loading children?
        $childrenList = $structureManager->getElementsChildren($this->id, $roles, $linkType, $allowedTypes,
            $restrictLinkTypes);

        return $childrenList;
    }

    public function getRelatedLanguageElement()
    {
        if ($this->languageElement === null) {
            $this->languageElement = false;
            if ($this->structureType == 'language') {
                $this->languageElement = $this;
            } else {
                $structureManager = $this->getService('structureManager');
                for ($parentElement = $this; $parentElement != false; $parentElement = $structureManager->getElementsFirstParent($parentElement->id)) {
                    if ($parentElement->structureType == 'language') {
                        $this->languageElement = $parentElement;
                        break;
                    }
                }
            }
        }
        return $this->languageElement;
    }

    /**
     * Get allowed children structure elements type according to settings and current user's privileges
     *
     * @param string $currentAction
     * @return string[]
     */
    public function getAllowedTypes($currentAction = 'showFullList')
    {
        if (!isset($this->allowedTypesByAction[$currentAction])) {
            $this->allowedTypesByAction[$currentAction] = [];

            $childCreationAction = 'showForm';
            /**
             * @var structureManager $structureManager
             */
            $structureManager = $this->getService('structureManager');
            $privilegesManager = $this->getService(privilegesManager::class);
            $privileges = $privilegesManager->getElementPrivileges($this->id);

            foreach ($this->allowedTypes as $type) {
                if (!$structureManager->getPrivilegeChecking() || (isset($privileges[$type]) && isset($privileges[$type][$childCreationAction]) && $privileges[$type][$childCreationAction] === true)) {
                    $this->allowedTypesByAction[$currentAction][] = $type;
                }
            }
        }
        return $this->allowedTypesByAction[$currentAction];
    }

    /**
     * todo: remove this method, it's only a quick workaround to ensure it's absence doesn't invoke the fatal error.
     *
     * @return array
     */
    public function getSubMenuList($linkType = 'structure')
    {
        $subMenus = [];
        $children = $this->getChildrenList('container', $linkType);
        foreach ($children as $child) {
            if ($child->hidden) {
                continue;
            }
            $subMenus[] = $child;
        }
        return $subMenus;
    }

    /**
     * @return string|null
     */
    public function getUrl($action = null)
    {
        if ($action) {
            return $this->URL . 'id:' . $this->id . '/action:' . $action . '/';
        }
        return $this->URL;
    }

    public function getNewElementUrl()
    {
        return $this->URL;
    }

    /**
     * Returns name of SQL table with module data for this module
     *
     * @return string
     */
    public function getDataResourceName()
    {
        return $this->dataResourceName;
    }

    public function copyExtraData($oldId)
    {
        $allChunks = $this->getAllDataChunks();
        foreach ($allChunks as $languageId => $chunks) {
            foreach ($chunks as $dataChunk) {
                if ($dataChunk instanceof ElementStorageValueHolderInterface) {
                    $oldValue = $dataChunk->getElementStorageValue();
                    if ($dataChunk instanceof ExtraDataHolderDataChunkInterface) {
                        if ($dataChunk->copyExtraData($oldValue, $oldId, $this->id)) {
                            if ($dataObject = $this->getModuleDataObject($languageId)) {
                                $propertyName = $dataChunk->fieldName;
                                $dataObject->$propertyName = $dataChunk->getElementStorageValue();
                            }
                        }
                    }
                }
            }
        }
    }

    public function getTitle()
    {
        if ($this->title || $this->title === '0') {
            return $this->title;
        } elseif ($translation = $this->getService(translationsManager::class)
            ->getTranslationByName('element.' . $this->structureType, 'adminTranslations', false)
        ) {
            return $translation . ' (' . $this->id . ')';
        }
        return $this->structureName;
    }


    public function getParentLanguagesGroupName()
    {
        return $this->languagesParentElementMarker;
    }

    /**
     * @param int $currentParentElementId
     */
    public function setCurrentParentElementId($currentParentElementId)
    {
        $this->currentParentElementId = $currentParentElementId;
    }

    public function getCurrentParentElement()
    {
        if ($this->currentParentElement === null) {
            /**
             * @var structureManager $structureManager
             */
            $structureManager = $this->getService('structureManager');
            if ($this->currentParentElementId) {
                $this->currentParentElement = $structureManager->getElementById($this->currentParentElementId);
            } elseif ($parentElements = $structureManager->getElementsParents($this->id)) {
                $this->currentParentElement = false;
                foreach ($parentElements as $parent) {
                    if (!$this->currentParentElement) {
                        $this->currentParentElement = $parent;
                    }
                    if ($parent->requested) {
                        $this->currentParentElement = $parent;
                        break;
                    }
                }
            }
        }
        return $this->currentParentElement;
    }

    public function getFirstParentElement()
    {
        return $this->getService('structureManager')->getElementsFirstParent($this->id);
    }

    public function getRequestedParentElement(): ?structureElement
    {
        if ($parents = $this->getService('structureManager')->getElementsParents($this->id)) {
            foreach ($parents as $parent) {
                if ($parent->requested) {
                    return $parent;
                }
            }
        }
        return null;
    }

    public function getPrivileges()
    {
        $privilegesManager = $this->getService(privilegesManager::class);
        $privileges = $privilegesManager->getElementPrivileges($this->id);
        return $privileges[$this->structureType];
    }


    public function __serialize()
    {
        $this->getModuleDataObjects();
        $fields = [
            'structureDataObject',
            'structureFields',
            'moduleFields',
            'multiLanguageFields',
            'moduleDataObjects',
        ];
        $data = [];
        foreach ($fields as $field) {
            $data[$field] = $this->$field;
        }

        return $data;
    }

    public function __unserialize($data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * @param $type
     * @return ElementForm
     *
     */
    public function getForm($type)
    {
        $form = false;

        $isFound = false;
        $fileName = "form.$this->structureType" . ucfirst($type) . ".php";
        $pathsManager = $this->getService(PathsManager::class);
        $elementPath = $pathsManager->getRelativePath('structureElements') . $this->structureType . '/' . $fileName;
        $sharedPath = $pathsManager->getRelativePath('sharedActions') . 'form.shared' . ucfirst($type) . '.php';
        if (!$isFound && $fileName = $pathsManager->getIncludeFilePath($elementPath)) {
            $isFound = true;
            $className = ucfirst($this->structureType) . ucfirst($type) . 'Structure';
        } elseif (!$isFound && $fileName = $pathsManager->getIncludeFilePath($sharedPath)) {
            $isFound = true;
            $className = 'Shared' . ucfirst($type) . 'Structure';
        } else {
            $this->logError('File not found ' . $fileName);
        }

        if ($isFound) {
            include_once($fileName);
            if (class_exists($className)) {
                /**
                 * @var ElementForm $form
                 */
                $form = new $className;
                $this->instantiateContext($form);
                $form->setElement($this);
                $form->setFormAction($this->getFormActionURL());
            } else {
                $this->logError('File for class "' . $className . '" not included');
            }
        }
        return $form;
    }

    /**
     * @return string
     */
    public function getActionName()
    {
        return $this->actionName;
    }

    public function getCreatedTimestamp(): int
    {
        return (int)$this->structureDataObject->dateCreated;
    }

    public function getModifiedTimestamp(): int
    {
        return (int)$this->structureDataObject->dateModified;
    }
}
