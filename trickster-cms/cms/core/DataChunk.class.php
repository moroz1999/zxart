<?php

use App\Paths\PathsManager;

abstract class DataChunk extends errorLogger implements DependencyInjectionContextInterface
{
    use DependencyInjectionContextTrait;
    protected $displayValue; //value for rendering template
    protected $formValue; //value for rendering forms.
    protected $storageValue; //storage value for persisting to database
    public $fieldName; //property name and name for database table field
    public $formError = false; //got error during validation

    public function __construct($fieldName)
    {
        $this->fieldName = $fieldName;
    }

    public function setFormValue($value)
    {
        $this->formValue = $value;
    }

    public function setProperties($properties)
    {
        foreach ($properties as $name => $value) {
            $this->$name = $value;
        }
    }

    /**
     * methods for validation of form data
     *
     * @param $validatorName
     * @return bool|validator
     */
    protected function constructValidator($validatorName)
    {
        $result = false;
        $className = $validatorName . 'Validator';
        if (!class_exists($className, false)) {
            $pathsManager = $this->getService(PathsManager::class);
            $fileDirectory = $pathsManager->getRelativePath('validators');
            if ($fileName = $pathsManager->getIncludeFilePath($fileDirectory . $validatorName . '.class.php')) {
                include_once($fileName);
            }
        }
        if (class_exists($className, false)) {
            $result = new $className();
            $this->instantiateContext($result);
        } else {
            $this->logError('File for validator "' . $className . '" is missing');
        }
        return $result;
    }

    public function validateFormData($validators)
    {
        $dataChunkValidated = true;
        foreach ($validators as &$validatorName) {
            if ($validator = $this->constructValidator($validatorName)) {
                if (!$validator->execute($this->formValue)) {
                    $dataChunkValidated = false;
                    $this->formError = true;
                }
            }
        }

        return $dataChunkValidated;
    }

    protected function getErrorLogLocation(): string
    {
        return $this->fieldName;
    }

    public function getStorageValue()
    {
        return $this->storageValue;
    }

    public function getFormValue()
    {
        if ($this->formValue === null) {
            $this->convertStorageToForm();
        }
        return $this->formValue;
    }

    public function setStorageValue($value)
    {
        $this->storageValue = $value;
        $this->convertStorageToDisplay();
    }

    public function getDisplayValue()
    {
        if ($this->displayValue === null) {
            $this->convertStorageToDisplay();
        }
        return $this->displayValue;
    }

    abstract public function setExternalValue($value);

    abstract public function convertStorageToDisplay();

    abstract public function convertStorageToForm();

    abstract public function convertFormToStorage();

    public function __serialize()
    {
        $fields = [
            'displayValue',
            'formValue',
            'storageValue',
            'fieldName',
            'formError',
        ];
        $data = [];
        foreach ($fields as $field){
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
}

/**
 * Interface ElementStorageValueHolderInterface
 *
 * This interface is used to announce that datachunk value should be stored in element's table column. Non used for files, images, arrays
 * Basic implementation is ElementStorageValueDataChunkTrait
 */
interface ElementStorageValueHolderInterface
{
    public function getElementStorageValue();

    public function setElementStorageValue($structureElement);
}

/**
 * Trait ElementTableDataChunk
 * If datachunk uses a column from module's DB table, then this method should be implemented. Non-used for datachunks stored in their own tables or not stored at all
 */
trait ElementStorageValueDataChunkTrait
{
    public function getElementStorageValue()
    {
        return $this->getStorageValue();
    }

    public function setElementStorageValue($structureElement)
    {
        $this->setStorageValue($structureElement);
    }
}

interface ExtraDataHolderDataChunkInterface
{
    public function persistExtraData();

    public function deleteExtraData();

    public function copyExtraData($oldValue, $oldId, $newId);
}


