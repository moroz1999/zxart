<?php

class articleElement extends menuDependantStructureElement
{
    use ConfigurableLayoutsProviderTrait;
    public $dataResourceName = 'module_article';
    protected $allowedTypes = ['subArticle'];
    public $defaultActionName = 'show';
    public $role = 'content';

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['hideTitle'] = 'checkbox';
        $moduleStructure['content'] = 'html';
        $moduleStructure['originalName'] = 'fileName';
        $moduleStructure['image'] = 'image';
        $moduleStructure['layout'] = 'text';
        $moduleStructure['subLayout'] = 'text';
    }

    public function getSubArticles()
    {
        /**
         * @var structureManager $structureManager
         */
        $structureManager = $this->getService('structureManager');
        $subArticles = $structureManager->getElementsChildren($this->id);
        return $subArticles;
    }

    public function getAllowedTypes($currentAction = 'showForm')
    {
        return $this->allowedTypes;
    }
}
