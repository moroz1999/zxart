<?php

class publicAddParty extends structureElementAction
{
    protected $loggable = true;

    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            $structureElement->prepareActualData();

            if ($structureElement->structureName == '') {
                $structureElement->structureName = $structureElement->title;
            }
			if (!is_null($structureElement->getDataChunk("image")->originalName)) {
				$structureElement->image = $structureElement->id;
				$structureElement->originalName = $structureElement->getDataChunk("image")->originalName;
			}

            $structureElement->persistElementData();

            $user = $this->getService('user');
            $privilegesManager = $this->getService('privilegesManager');
            $privilegesManager->setPrivilege($user->id, $structureElement->id, 'party', 'showPublicForm', 'allow');
            $privilegesManager->setPrivilege($user->id, $structureElement->id, 'party', 'publicReceive', 'allow');
            $privilegesManager->setPrivilege($user->id, $structureElement->id, 'party', 'publicDelete', 'allow');
            $privilegesManager->setPrivilege($user->id, $structureElement->id, 'party', 'deleteFile', 'allow');
            $user->refreshPrivileges();

            $structureElement->recalculate();

            $controller->redirect($structureElement->URL);
        }

        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'title',
            'abbreviation',
            'structureName',
            'city',
            'country',
			'image',
		];
    }

    public function setValidators(&$validators)
    {
    }
}

