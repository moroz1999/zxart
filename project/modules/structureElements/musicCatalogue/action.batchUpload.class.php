<?php

class batchUploadMusicCatalogue extends structureElementAction
{
    protected $loggable = true;

    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($musicsInfo = $structureElement->music) {
            $pathsManager = $this->getService('PathsManager');
            $cachePath = $pathsManager->getPath('uploadsCache');
            $pathsManager->ensureDirectory($cachePath);

            foreach ($musicsInfo as $musicInfo) {
                $zxMusicElement = $structureManager->createElement('zxMusic', 'showForm', $structureElement->id);
                $temporaryFile = $cachePath . basename($musicInfo['tmp_name']);
                $originalFileName = $musicInfo['name'];

                $zxMusicElement->prepareActualData();
                $info = pathinfo($originalFileName);
                $zxMusicElement->title = str_replace('_', ' ', ucfirst(ucfirst($info['filename'])));
                $zxMusicElement->structureName = $zxMusicElement->title;

                $zxMusicElement->description = $structureElement->description;
                $zxMusicElement->tagsText = $structureElement->tagsText;
                $zxMusicElement->year = $structureElement->year;
                $zxMusicElement->file = $zxMusicElement->id;
                $zxMusicElement->fileName = $originalFileName;
                $zxMusicElement->game = $structureElement->game;
                $zxMusicElement->party = $structureElement->party;
                $zxMusicElement->compo = $structureElement->compo;
                $zxMusicElement->author = $structureElement->author;
                $zxMusicElement->dateAdded = $zxMusicElement->dateCreated;
                $zxMusicElement->userId = $this->getService('user')->id;
                $zxMusicElement->chipType = $structureElement->chipType;
                $zxMusicElement->channelsType = $structureElement->channelsType;
                $zxMusicElement->frequency = $structureElement->frequency;
                $zxMusicElement->intFrequency = $structureElement->intFrequency;
                $zxMusicElement->formatGroup = $structureElement->formatGroup;

                $zxMusicElement->persistElementData();

                copy($temporaryFile, $this->getService('PathsManager')->getPath('uploads') . $zxMusicElement->file);
                unlink($temporaryFile);

                $zxMusicElement->renewPartyLink();
                $zxMusicElement->renewAuthorLink();
                $zxMusicElement->updateProdLink();
                $zxMusicElement->updateTagsInfo();
                $zxMusicElement->updateYear();

                $zxMusicElement->persistElementData();
                $zxMusicElement->logCreation();
            }
        }
        $controller->redirect($structureElement->URL);
    }

    public function setExpectedFields(&$expectedFields): void
    {
        $expectedFields = [
            'music',
            'game',
            'compo',
            'author',
            'year',
            'party',
            'type',
            'music',
            'tagsText',
            'description',
            'chipType',
            'channelsType',
            'formatGroup',
        ];
    }
}

