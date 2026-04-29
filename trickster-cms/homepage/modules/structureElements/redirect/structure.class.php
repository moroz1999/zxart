<?php

/**
 * Class redirectElement
 *
 * @property string sourceUrl
 * @property string destinationUrl
 * @property int destinationElementId
 * @property int partialMatch
 *
 */
class redirectElement extends structureElement
{
    use SearchTypesProviderTrait;
    public $dataResourceName = "module_redirect";
    protected $allowedTypes = [];
    public $defaultActionName = "showForm";
    public $role = "content";
    protected $destinationElement;

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure["sourceUrl"] = "text";
        $moduleStructure["destinationUrl"] = "url";
        $moduleStructure["destinationElementId"] = "text";
        $moduleStructure["partialMatch"] = "checkbox";
    }

    public function getDestinationElement()
    {
        if (is_null($this->destinationElement)) {
            $this->destinationElement = false;
            if ($this->destinationElementId) {
                $structureManager = $this->getService('structureManager');
                $this->destinationElement = $structureManager->getElementById($this->destinationElementId);
            }
        }
        return $this->destinationElement;
    }

    public function getTitle()
    {
        return ($this->sourceUrl) ? $this->sourceUrl : parent::getTitle();
    }
}