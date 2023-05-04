<?php

class publicReceiveZxPicture extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param zxPictureElement $structureElement
     * @return mixed|void
     */
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
                $structureElement->image = $structureElement->id;
                $structureElement->originalName = $structureElement->getDataChunk("image")->originalName;
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
            $structureElement->renewOriginalAuthorLink();
            $structureElement->updateProdLink();
            $structureElement->updateTagsInfo();
            $structureElement->updateYear();
            $structureElement->checkGameTag();

            $structureElement->persistElementData();
            $structureElement->updateMd5($this->getService('PathsManager')->getPath('uploads') . $structureElement->image, $structureElement->originalName);
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
            'originalAuthor',
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

