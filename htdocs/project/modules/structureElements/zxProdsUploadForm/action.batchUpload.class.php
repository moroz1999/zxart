<?php

class batchUploadZxProdsUploadForm extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param zxProdsUploadFormElement $structureElement
     * @return mixed|void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $firstProd = null;
        if ($filesInfo = $structureElement->file) {
            $privilegesManager = $this->getService('privilegesManager');
            $linksManager = $this->getService('linksManager');
            $user = $this->getService('user');

            $cachePath = $this->getService('PathsManager')->getPath('uploadsCache');

            foreach ($filesInfo as $imageInfo) {
                /**
                 * @var zxProdElement $zxProdElement
                 */
                if ($zxProdElement = $structureManager->createElement('zxProd', 'show', $structureElement->id)) {
                    if (!$firstProd) {
                        $firstProd = $zxProdElement;
                    }
                    $originalFileName = $imageInfo['name'];
                    $info = pathinfo($originalFileName);

                    if ($structureElement->prodTitle) {
                        $zxProdElement->title = $structureElement->prodTitle;
                    } else {
                        $zxProdElement->title = str_replace('_', ' ', ucfirst(ucfirst($info['filename'])));
                    }

                    $zxProdElement->structureName = $zxProdElement->title;
                    $zxProdElement->externalLink = $structureElement->externalLink;
                    $zxProdElement->party = $structureElement->party;
                    $zxProdElement->compo = $structureElement->compo;
                    $zxProdElement->partyplace = $structureElement->partyplace;
                    $zxProdElement->groups = $structureElement->groups;
                    $zxProdElement->categories = $structureElement->categories;
                    $zxProdElement->publishers = $structureElement->publishers;
                    $zxProdElement->year = $structureElement->year;
                    $zxProdElement->description = $structureElement->description;
                    $zxProdElement->denyVoting = $structureElement->denyVoting;
                    $zxProdElement->denyComments = $structureElement->denyComments;
                    $zxProdElement->legalStatus = $structureElement->legalStatus;
                    $zxProdElement->language = $structureElement->language;
                    $zxProdElement->youtubeId = $structureElement->youtubeId;
                    $zxProdElement->tagsText = $structureElement->tagsText;
                    $zxProdElement->addAuthor = $structureElement->addAuthor;
                    $zxProdElement->addAuthorRole = $structureElement->addAuthorRole;
                    $zxProdElement->connectedFile = $structureElement->connectedFile;
                    $zxProdElement->mapFilesSelector = $structureElement->mapFilesSelector;

                    $zxProdElement->dateAdded = $zxProdElement->dateCreated;
                    $zxProdElement->userId = $this->getService('user')->id;

                    if (!$zxProdElement->categories) {
                        $zxProdElement->categories = [92188];
                    }

                    $zxProdElement->checkLinks('categories', 'zxProdCategory');

                    $zxProdElement->renewPartyLink();
                    $zxProdElement->updateTagsInfo();
                    $zxProdElement->updateYear();
                    $zxProdElement->persistAuthorship('prod');

                    $zxProdElement->executeAction('receiveFiles');

                    $zxProdElement->persistElementData();
                    $zxProdElement->logCreation();

                    $linksManager->unLinkElements($structureElement->id, $zxProdElement->getId(), 'structure');

                    foreach ($this->getPrivileges() as $privilege) {
                        $privilegesManager->setPrivilege(
                            $user->id,
                            $zxProdElement->getId(),
                            $privilege[0],
                            $privilege[1],
                            $privilege[2]
                        );
                    }

                    /**
                     * @var zxReleaseElement $zxReleaseElement
                     */
                    if ($zxReleaseElement = $structureManager->createElement(
                        'zxRelease',
                        'show',
                        $zxProdElement->getId()
                    )) {
                        if ($zxProdElement->title) {
                            $zxReleaseElement->title = $zxProdElement->title;
                        } else {
                            $zxReleaseElement->title = str_replace('_', ' ', ucfirst(ucfirst($info['filename'])));
                        }

                        $zxReleaseElement->structureName = $zxReleaseElement->title;
                        $zxReleaseElement->file = $zxReleaseElement->getId();
                        $zxReleaseElement->dateAdded = $zxReleaseElement->dateCreated;
                        $zxReleaseElement->userId = $this->getService('user')->id;

                        $zxReleaseElement->persistElementData();

                        if ($temporaryFile = $cachePath . basename($imageInfo['tmp_name'])) {
                            $zxReleaseElement->fileName = $imageInfo['name'];
                            copy(
                                $temporaryFile,
                                $this->getService('PathsManager')->getPath('releases') . $zxReleaseElement->file
                            );
                            unlink($temporaryFile);
                        }

                        $zxReleaseElement->persistElementData();
                        $zxReleaseElement->updateFileStructure();
                    }

                }
            }
            $user->refreshPrivileges();
        }
        $controller->redirect($firstProd->URL);
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'prodTitle',
            'party',
            'partyplace',
            'compo',
            'year',
            'description',
            'categories',
            'publishers',
            'groups',
            'denyVoting',
            'denyComments',
            'legalStatus',
            'externalLink',
            'language',
            'file',
            'youtubeId',
            'tagsText',
            'addAuthor',
            'addAuthorRole',
            'connectedFile',
            'mapFilesSelector',
        ];
    }

    private function getPrivileges(): array
    {
        return [
            ['zxProd', 'showPublicForm', 'allow'],
            ['zxProd', 'publicReceive', 'allow'],
            ['zxProd', 'publicDelete', 'allow'],
            ['zxProd', 'deleteFile', 'allow'],
            ['zxProd', 'deleteAuthor', 'allow'],
            ['zxProd', 'submitTags', 'allow'],
            ['zxProd', 'receiveFiles', 'allow'],
            ['zxRelease', 'showPublicForm', 'allow'],
            ['zxRelease', 'publicReceive', 'allow'],
            ['zxRelease', 'publicDelete', 'allow'],
            ['zxRelease', 'deleteFile', 'allow'],
            ['zxRelease', 'deleteAuthor', 'allow'],
            ['zxRelease', 'submitTags', 'allow'],
            ['file', 'delete', 'allow'],
            ['file', 'receive', 'allow'],
        ];
    }
}

