<?php

use App\Users\CurrentUser;

class publicAddAuthor extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param authorElement $structureElement
     *
     * @return void
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
                $structureElement->image = $structureElement->getId();
                $structureElement->originalName = $structureElement->getDataChunk("image")->originalName;
            }

            $structureElement->persistElementData();
            $structureElement->checkParentLetter();

            $structureElement->recalculatePicturesData();
            $structureElement->recalculateMusicData();
            $structureElement->recalculate();

            $privilegesManager = $this->getService('privilegesManager');
            $user = $this->getService(CurrentUser::class);
            $privilegesManager->setPrivilege($user->id, $structureElement->getId(), 'author', 'showPublicForm', 'allow');
            $privilegesManager->setPrivilege($user->id, $structureElement->getId(), 'author', 'publicReceive', 'allow');
            $privilegesManager->setPrivilege($user->id, $structureElement->getId(), 'author', 'publicDelete', 'allow');
            $privilegesManager->setPrivilege($user->id, $structureElement->getId(), 'author', 'deleteFile', 'allow');
            $user->refreshPrivileges();

            $controller->redirect($structureElement->URL);
        }
        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields): void
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

    public function setValidators(&$validators): void
    {
    }
}


