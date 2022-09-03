<?php

class publicReceiveZxPicture extends structureElementAction
{
    protected $loggable = true;

    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            $structureElement->prepareActualData();

            if (trim($structureElement->title) == '') {
                if ($game = $structureElement->getReleaseElement()) {
                    $structureElement->title = $game->title;
                } else {
                    if (!is_null($structureElement->getDataChunk("image")->originalName)) {
                        $info = pathinfo($structureElement->getDataChunk("image")->originalName);
                        $structureElement->title = $info['filename'];
                        $structureElement->title = ucfirst($structureElement->title);
                        $structureElement->title = str_replace('_', ' ', ucfirst($structureElement->title));
                    }
                }
            }
            $structureElement->dateAdded = $structureElement->dateCreated;
            if (!is_null($structureElement->getDataChunk("image")->originalName)) {
                $cachePath = $this->getService('PathsManager')->getPath('uploadsCache');

                $structureElement->image = $structureElement->id;
                $structureElement->originalName = $structureElement->getDataChunk("image")->originalName;
                $structureElement->calculateMd5($cachePath . $structureElement->getDataChunk("image")->temporaryName);
            }
            if (!is_null($structureElement->getDataChunk("inspired")->originalName)) {
                $structureElement->inspired = $structureElement->id . '_inspired';
                $structureElement->inspiredName = $structureElement->getDataChunk("inspired")->originalName;
            }
            if (!is_null($structureElement->getDataChunk("inspired2")->originalName)) {
                $structureElement->inspired2 = $structureElement->id . '_inspired2';
                $structureElement->inspired2Name = $structureElement->getDataChunk("inspired2")->originalName;
            }
            if (!is_null($structureElement->getDataChunk("exeFile")->originalName)) {
                $structureElement->exeFile = $structureElement->id . '_exe';
                $structureElement->exeFileName = $structureElement->getDataChunk("exeFile")->originalName;
            }
            if (!is_null($structureElement->getDataChunk("sequence")->originalName)) {
                $structureElement->sequence = $structureElement->id . '_sequence';
                $structureElement->sequenceName = $structureElement->getDataChunk("sequence")->originalName;
            }
            $structureElement->structureName = $structureElement->title;

            if (!$structureElement->userId) {
                $structureElement->userId = $this->getService('user')->id;
            }

            //if no author is selected, select automatically Unknown author
            if (!$structureElement->author) {
                $structureElement->author = [$this->getService('ConfigManager')->get('zx.unknownAuthorId')];
            }

            $structureElement->renewPartyLink();
            $structureElement->renewAuthorLink();
            $structureElement->updateProdLink();
            $structureElement->updateTagsInfo();
            $structureElement->updateYear();
            $structureElement->checkGameTag();

            $structureElement->persistElementData();

            $controller->redirect($structureElement->URL);
        } else {
            $structureElement->setViewName('form');
        }
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'title',
            'border',
            'game',
            'compo',
            'author',
            'year',
            'party',
            'partyplace',
            'type',
            'image',
            'inspired',
            'inspired2',
            'exeFile',
            'sequence',
            'tagsText',
            'description',
            'rotation',
            'denyVoting',
            'denyComments',
            'palette',
        ];
    }

    public function setValidators(&$validators)
    {
    }
}

