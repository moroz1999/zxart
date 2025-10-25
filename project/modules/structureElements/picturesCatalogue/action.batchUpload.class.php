<?php

class batchUploadPicturesCatalogue extends structureElementAction
{
    protected $loggable = true;

    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($imagesInfo = $structureElement->image) {
            $cachePath = $this->getService('PathsManager')->getPath('uploadsCache');
            foreach ($imagesInfo as $imageInfo) {
                $pictureElement = $structureManager->createElement('zxPicture', 'showForm', $structureElement->getId());
                $temporaryFile = $cachePath . basename($imageInfo['tmp_name']);
                $originalFileName = $imageInfo['name'];

                $pictureElement->prepareActualData();
                $info = pathinfo($originalFileName);
                $pictureElement->title = str_replace('_', ' ', ucfirst(ucfirst($info['filename'])));
                $pictureElement->structureName = $pictureElement->title;

                $pictureElement->compo = $structureElement->compo;
                $pictureElement->description = $structureElement->description;
                $pictureElement->tagsText = $structureElement->tagsText;
                $pictureElement->year = $structureElement->year;
                $pictureElement->image = $pictureElement->getId();
                $pictureElement->originalName = $originalFileName;
                $pictureElement->type = $structureElement->type;
                $pictureElement->border = $structureElement->border;
                $pictureElement->border = $structureElement->border;
                $pictureElement->game = $structureElement->game;
                $pictureElement->party = $structureElement->party;
                $pictureElement->author = $structureElement->author;
                $pictureElement->dateAdded = $pictureElement->dateCreated;
                $pictureElement->userId = $this->getService('user')->id;

                $pictureElement->persistElementData();

                copy($temporaryFile, $this->getService('PathsManager')->getPath('uploads') . $pictureElement->image);
                unlink($temporaryFile);

                $pictureElement->renewPartyLink();
                $pictureElement->renewAuthorLink();
                $pictureElement->updateProdLink();
                $pictureElement->updateTagsInfo();
                $pictureElement->updateYear();
                $pictureElement->checkGameTag();

                $pictureElement->persistElementData();
                $pictureElement->logCreation();
            }
        }
        $controller->redirect($structureElement->URL);
    }

    public function setExpectedFields(&$expectedFields): void
    {
        $expectedFields = [
            'border',
            'game',
            'compo',
            'author',
            'year',
            'party',
            'type',
            'image',
            'tagsText',
            'description',
        ];
    }
}

