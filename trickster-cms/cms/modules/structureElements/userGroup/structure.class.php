<?php

class userGroupElement extends structureElement
{
    public $dataResourceName = 'module_user_group';
    public $defaultActionName = 'showForm';
    public $role = 'content';
    public $linkExists = false;
    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['groupName'] = 'text';
        $moduleStructure['description'] = 'text';
    }

    public function getUsers()
    {
        return $this->getChildrenList(null, 'userRelation');
    }

    public function getTitle()
    {
        if ($this->description) {
            return $this->description;
        } elseif ($this->groupName) {
            return $this->groupName;
        } else {
            return parent::getTitle();
        }
    }
}