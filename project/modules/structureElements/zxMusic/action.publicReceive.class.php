<?php

class publicReceiveZxMusic extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param zxMusicElement $structureElement
     *
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            $structureElement->prepareActualData();

            if (!is_null($structureElement->getDataChunk("file")->originalName)) {
                $structureElement->mp3Name = null;
            }

            if (trim($structureElement->title) == '') {
                if ($game = $structureElement->getReleaseElement()) {
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
            }
            if (!is_null($structureElement->getDataChunk("trackerFile")->originalName)) {
                $structureElement->trackerFile = $structureElement->id . '_tracker';
                $structureElement->trackerFileName = $structureElement->getDataChunk("trackerFile")->originalName;
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
            $structureElement->updateMd5($this->getService('PathsManager')->getPath('uploads') . $structureElement->file, $structureElement->fileName);

            $controller->redirect($structureElement->URL);
        } else {
            $structureElement->setViewName('form');
        }
    }

    public function setExpectedFields(&$expectedFields): void
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

    public function setValidators(&$validators): void
    {
    }
}

