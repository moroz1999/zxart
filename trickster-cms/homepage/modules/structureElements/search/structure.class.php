<?php

class searchElement extends menuDependantStructureElement implements MetadataProviderInterface
{
    use SearchTypesProviderTrait;

    public $dataResourceName = 'module_search';
    public $defaultActionName = 'show';
    protected $allowedTypes = [];
    public $role = 'container';
    const DEFAULT_PAGE_SIZE = 50;

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['phrase'] = 'text';

        $moduleStructure['bAjaxSearch'] = 'text';
    }

    public function getMetaTitle()
    {
        return $this->phrase ? $this->title . ' (' . $this->phrase . ')' : $this->title;
    }

    public function getCanonicalUrl()
    {
        return $this->URL . 'action:perform/id:' . $this->id . '/phrase:' . $this->phrase . '/';
    }

    public function getMetaDenyIndex()
    {
        return true;
    }
}
