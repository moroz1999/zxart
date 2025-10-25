<?php

use ZxArt\Authors\Constants;

class batchUploadMusicUploadForm extends structureElementAction
{
    protected $loggable = true;

    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($musicsInfo = $structureElement->music) {
            $privilegesManager = $this->getService('privilegesManager');
            $linksManager = $this->getService('linksManager');
            $user = $this->getService('user');

            $currentElement = $structureManager->getElementsFirstParent($structureElement->getId());
            if ($musicCatalogueId = $structureManager->getElementIdByMarker('musicCatalogue')) {
                $pathsManager = $this->getService('PathsManager');
                $cachePath = $pathsManager->getPath('uploadsCache');
                $pathsManager->ensureDirectory($cachePath);

                foreach ($musicsInfo as $musicInfo) {
                    $zxMusicElement = $structureManager->createElement('zxMusic', 'show', $structureElement->getId());
                    $temporaryFile = $cachePath . basename($musicInfo['tmp_name']);
                    $originalFileName = $musicInfo['name'];

                    $zxMusicElement->prepareActualData();
                    $info = pathinfo($originalFileName);
                    if ($structureElement->musicTitle) {
                        $zxMusicElement->title = $structureElement->musicTitle;
                    } else {
                        $zxMusicElement->title = str_replace('_', ' ', ucfirst(ucfirst($info['filename'])));
                    }
                    $zxMusicElement->structureName = $zxMusicElement->title;

                    $zxMusicElement->description = $structureElement->description;
                    $zxMusicElement->tagsText = $structureElement->tagsText;
                    $zxMusicElement->year = $structureElement->year;
                    $zxMusicElement->file = $zxMusicElement->getId();
                    $zxMusicElement->fileName = $originalFileName;
                    $zxMusicElement->game = $structureElement->game;
                    $zxMusicElement->party = $structureElement->party;
                    $zxMusicElement->partyplace = $structureElement->partyplace;
                    $zxMusicElement->compo = $structureElement->compo;
                    $zxMusicElement->dateAdded = $zxMusicElement->dateCreated;
                    $zxMusicElement->userId = $this->getService('user')->id;
                    $zxMusicElement->chipType = $structureElement->chipType;
                    $zxMusicElement->frequency = $structureElement->frequency;
                    $zxMusicElement->intFrequency = $structureElement->intFrequency;
                    $zxMusicElement->channelsType = $structureElement->channelsType;
                    $zxMusicElement->formatGroup = $structureElement->formatGroup;
                    $zxMusicElement->denyVoting = $structureElement->denyVoting;
                    $zxMusicElement->denyComments = $structureElement->denyComments;
                    if (!$structureElement->author) {
                        $zxMusicElement->author = [Constants::UNKNOWN_ID];
                    } else {
                        $zxMusicElement->author = $structureElement->author;
                    }
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
                    $linksManager->unLinkElements($currentElement->getId(), $zxMusicElement->getId());
                    $linksManager->linkElements($musicCatalogueId, $zxMusicElement->getId());

                    $privilegesManager->setPrivilege(
                        $user->id,
                        $zxMusicElement->getId(),
                        'zxMusic',
                        'showPublicForm',
                        'allow'
                    );
                    $privilegesManager->setPrivilege(
                        $user->id,
                        $zxMusicElement->getId(),
                        'zxMusic',
                        'publicReceive',
                        'allow'
                    );
                    $privilegesManager->setPrivilege(
                        $user->id,
                        $zxMusicElement->getId(),
                        'zxMusic',
                        'publicDelete',
                        'allow'
                    );
                    $privilegesManager->setPrivilege($user->id, $zxMusicElement->getId(), 'zxMusic', 'deleteFile', 'allow');
                    $privilegesManager->setPrivilege($user->id, $zxMusicElement->getId(), 'zxMusic', 'submitTags', 'allow');
                    $user->refreshPrivileges();
                }
            }
        }

        $controller->redirect($structureElement->URL);
    }

    public function setExpectedFields(&$expectedFields): void
    {
        $expectedFields = [
            'musicTitle',
            'music',
            'game',
            'compo',
            'author',
            'year',
            'party',
            'partyplace',
            'type',
            'music',
            'tagsText',
            'description',
            'chipType',
            'channelsType',
            'frequency',
            'intFrequency',
            'formatGroup',
            'denyVoting',
            'denyComments',
        ];
    }
}

