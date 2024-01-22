<?php

class publicAddAuthor extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param authorElement $structureElement
     * @return mixed|void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            $structureElement->prepareActualData();

            if ($structureElement->title == '') {
                $structureElement->title = $structureElement->realName;
            }
            $structureElement->structureName = $structureElement->title;

            if (!is_null($structureElement->getDataChunk("image")->originalName)) {
                $structureElement->image = $structureElement->id;
                $structureElement->originalName = $structureElement->getDataChunk("image")->originalName;
            }

            $structureElement->persistElementData();
            $structureElement->checkParentLetter();

            $structureElement->recalculatePicturesData();
            $structureElement->recalculateMusicData();
            $structureElement->recalculateAuthorData();

            $privilegesManager = $this->getService('privilegesManager');
            $user = $this->getService('user');
            $privilegesManager->setPrivilege($user->id, $structureElement->id, 'author', 'showPublicForm', 'allow');
            $privilegesManager->setPrivilege($user->id, $structureElement->id, 'author', 'publicReceive', 'allow');
            $privilegesManager->setPrivilege($user->id, $structureElement->id, 'author', 'publicDelete', 'allow');
            $privilegesManager->setPrivilege($user->id, $structureElement->id, 'author', 'deleteFile', 'allow');
            $user->refreshPrivileges();

            $controller->redirect($structureElement->URL);
        }
        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'title',
            'realName',
            'country',
            'city',
            'wikiLink',
            'image',
            'denyVoting',
            'denyComments',
            'deny3a',
            'artCityId',
            'displayInMusic',
            'displayInGraphics',
            'chipType',
            'channelsType',
            'frequency',
            'intFrequency',
            'palette',
            'zxTunesId',
        ];
    }

    public function setValidators(&$validators)
    {
    }
}


