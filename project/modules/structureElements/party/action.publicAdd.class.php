<?php

class publicAddParty extends structureElementAction
{
    protected $loggable = true;

    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            $structureElement->prepareActualData();

            if ($structureElement->structureName == '') {
                $structureElement->structureName = $structureElement->title;
            }
			if (!is_null($structureElement->getDataChunk("image")->originalName)) {
				$structureElement->image = $structureElement->getId();
				$structureElement->originalName = $structureElement->getDataChunk("image")->originalName;
			}

            $structureElement->persistElementData();

            $user = $this->getService('user');
            $privilegesManager = $this->getService('privilegesManager');
            $privilegesManager->setPrivilege($user->id, $structureElement->getId(), 'party', 'showPublicForm', 'allow');
            $privilegesManager->setPrivilege($user->id, $structureElement->getId(), 'party', 'publicReceive', 'allow');
            $privilegesManager->setPrivilege($user->id, $structureElement->getId(), 'party', 'publicDelete', 'allow');
            $privilegesManager->setPrivilege($user->id, $structureElement->getId(), 'party', 'deleteFile', 'allow');
            $user->refreshPrivileges();

            $structureElement->recalculate();

            $controller->redirect($structureElement->URL);
        }

        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields): void
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

    public function setValidators(&$validators): void
    {
    }
}

