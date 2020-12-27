<?php

class batchUploadPicturesUploadForm extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param picturesUploadFormElement $structureElement
     * @return mixed|void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($imagesInfo = $structureElement->image) {
            $privilegesManager = $this->getService('privilegesManager');
            $linksManager = $this->getService('linksManager');
            $user = $this->getService('user');

            $currentElement = $structureManager->getElementsFirstParent($structureElement->id);

            if ($picturesCatalogueId = $structureManager->getElementIdByMarker('picturesCatalogue')) {
                $cachePath = $this->getService('PathsManager')->getPath('uploadsCache');

                foreach ($imagesInfo as $imageInfo) {
                    /**
                     * @var zxPictureElement $pictureElement
                     */
                    if ($pictureElement = $structureManager->createElement(
                        'zxPicture',
                        'show',
                        $structureElement->id
                    )) {
                        $temporaryFile = $cachePath . basename($imageInfo['tmp_name']);
                        $originalFileName = $imageInfo['name'];

                        $pictureElement->prepareActualData();
                        $info = pathinfo($originalFileName);

                        if ($structureElement->pictureTitle) {
                            $pictureElement->title = $structureElement->pictureTitle;
                        } else {
                            $pictureElement->title = str_replace('_', ' ', ucfirst(ucfirst($info['filename'])));
                        }

                        $pictureElement->structureName = $pictureElement->title;

                        $pictureElement->compo = $structureElement->compo;
                        $pictureElement->description = $structureElement->description;
                        $pictureElement->tagsText = $structureElement->tagsText;
                        $pictureElement->year = $structureElement->year;
                        $pictureElement->image = $pictureElement->id;
                        $pictureElement->originalName = $originalFileName;
                        $pictureElement->type = $structureElement->type;
                        $pictureElement->border = $structureElement->border;
                        $pictureElement->game = $structureElement->game;
                        $pictureElement->party = $structureElement->party;
                        $pictureElement->partyplace = $structureElement->partyplace;
                        $pictureElement->rotation = $structureElement->rotation;
                        $pictureElement->palette = $structureElement->palette;
                        $pictureElement->denyVoting = $structureElement->denyVoting;
                        $pictureElement->denyComments = $structureElement->denyComments;
                        if (!$structureElement->author) {
                            $pictureElement->author = [$this->getService('ConfigManager')->get('zx.unknownAuthorId')];
                        } else {
                            $pictureElement->author = $structureElement->author;
                        }
                        $pictureElement->dateAdded = $pictureElement->dateCreated;
                        $pictureElement->userId = $this->getService('user')->id;

                        $pictureElement->persistElementData();

                        copy(
                            $temporaryFile,
                            $this->getService('PathsManager')->getPath('uploads') . $pictureElement->image
                        );
                        unlink($temporaryFile);

                        $pictureElement->renewPartyLink();
                        $pictureElement->renewAuthorLink();
                        $pictureElement->updateProdLink();
                        $pictureElement->updateTagsInfo();
                        $pictureElement->updateYear();
                        $pictureElement->checkGameTag();

                        $pictureElement->persistElementData();
                        $pictureElement->logCreation();

                        $linksManager->unLinkElements($currentElement->id, $pictureElement->id);
                        $linksManager->linkElements($picturesCatalogueId, $pictureElement->id);

                        $privilegesManager->setPrivilege(
                            $user->id,
                            $pictureElement->id,
                            'zxPicture',
                            'showPublicForm',
                            'allow'
                        );
                        $privilegesManager->setPrivilege(
                            $user->id,
                            $pictureElement->id,
                            'zxPicture',
                            'publicReceive',
                            'allow'
                        );
                        $privilegesManager->setPrivilege(
                            $user->id,
                            $pictureElement->id,
                            'zxPicture',
                            'publicDelete',
                            'allow'
                        );
                        $privilegesManager->setPrivilege(
                            $user->id,
                            $pictureElement->id,
                            'zxPicture',
                            'deleteFile',
                            'allow'
                        );
                        $privilegesManager->setPrivilege(
                            $user->id,
                            $pictureElement->id,
                            'zxPicture',
                            'submitTags',
                            'allow'
                        );
                        $user->refreshPrivileges();
                    }
                }
            }
        }
        $controller->redirect($structureElement->URL);
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'pictureTitle',
            'border',
            'game',
            'compo',
            'author',
            'year',
            'party',
            'partyplace',
            'type',
            'image',
            'tagsText',
            'description',
            'rotation',
            'denyVoting',
            'denyComments',
        ];
    }
}

