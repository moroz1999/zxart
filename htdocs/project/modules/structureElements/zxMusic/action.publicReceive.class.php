<?php

class publicReceiveZxMusic extends structureElementAction
{
    protected $loggable = true;

    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            $structureElement->prepareActualData();

            if (!is_null($structureElement->getDataChunk("file")->originalName)) {
                $structureElement->mp3Name = null;
            }

            if (trim($structureElement->title) == '') {
                if ($game = $structureElement->getGameElement()) {
                    $structureElement->title = $game->title;
                } else {
                    if (!is_null($structureElement->getDataChunk("file")->originalName)) {
                        $info = pathinfo($structureElement->getDataChunk("file")->originalName);
                        $structureElement->title = str_replace('_', ' ', ucfirst($info['filename']));
                    }
                }
            }
            $structureElement->dateAdded = $structureElement->dateCreated;
            $cachePath = $this->getService('PathsManager')->getPath('uploadsCache');

            if (!is_null($structureElement->getDataChunk("file")->originalName)) {
                $structureElement->file = $structureElement->id;
                $structureElement->fileName = $structureElement->getDataChunk("file")->originalName;
                $structureElement->calculateMd5($cachePath . $structureElement->getDataChunk("file")->temporaryName);
            }
            if (!is_null($structureElement->getDataChunk("trackerFile")->originalName)) {
                $structureElement->trackerFile = $structureElement->id . '_tracker';
                $structureElement->trackerFileName = $structureElement->getDataChunk("trackerFile")->originalName;
                $structureElement->calculateMd5(
                    $cachePath . $structureElement->getDataChunk("trackerFile")->temporaryName
                );
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
            'game',
            'compo',
            'author',
            'year',
            'party',
            'partyplace',
            'file',
            'trackerFile',
            'inspired',
            'tagsText',
            'description',
            'denyPlaying',
            'denyVoting',
            'denyComments',
            'chipType',
            'frequency',
            'intFrequency',
            'channelsType',
            'embedCode',
            'formatGroup',
        ];
    }

    public function setValidators(&$validators)
    {
    }
}

