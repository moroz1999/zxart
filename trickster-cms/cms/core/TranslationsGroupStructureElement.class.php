<?php

abstract class TranslationsGroupStructureElement extends structureElement
{
    public $dataResourceName = 'module_generic';
    public $defaultActionName = 'showForm';
    public $role = 'content';
    /**
     * @var translationElement[]
     */
    protected $translationsList;
    /**
     * @var translationElement[]
     */
    protected $translationsIndex;

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
    }

    public function getTranslations()
    {
        if ($this->translationsList === null) {
            $this->translationsList = [];

            $structureManager = $this->getService('structureManager');
            if ($fields = $structureManager->getElementsChildren($this->id)) {
                $this->translationsList = $fields;
            }
        }
        return $this->translationsList;
    }

    public function getTranslationsIndex()
    {
        if ($this->translationsIndex === null) {
            $this->translationsIndex = [];
            foreach ($this->getTranslations() as $translation) {
                $this->translationsIndex[$translation->structureName] = $translation;
            }
        }
        return $this->translationsIndex;
    }

    /**
     * @param $name
     * @return translationElement
     */
    public function findTranslation($name)
    {
        $this->getTranslationsIndex();
        return isset($this->translationsIndex[$name])
            ? $this->translationsIndex[$name]
            : null;
    }

    public function deleteTranslation($name)
    {
        $name = strtolower($name);
        $this->getTranslationsIndex();
        if (isset($this->translationsIndex[$name])) {
            $this->translationsIndex[$name]->deleteElementData();
            unset($this->translationsIndex[$name]);
        }
    }
}