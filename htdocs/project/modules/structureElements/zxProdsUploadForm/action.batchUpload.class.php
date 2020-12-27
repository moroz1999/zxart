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
        if ($filesInfo = $structureElement->file) {
            $privilegesManager = $this->getService('privilegesManager');
            $linksManager = $this->getService('linksManager');
            $user = $this->getService('user');

            if ($prodsCatalogueId = $structureManager->getElementIdByMarker('zxProds')) {
                $cachePath = $this->getService('PathsManager')->getPath('uploadsCache');

                foreach ($filesInfo as $imageInfo) {
                    /**
                     * @var zxProdElement $zxProdElement
                     */
                    if ($zxProdElement = $structureManager->createElement('zxProd', 'show', $structureElement->id)) {
                        $originalFileName = $imageInfo['name'];
                        $info = pathinfo($originalFileName);

                        if ($structureElement->prodTitle) {
                            $zxProdElement->title = $structureElement->prodTitle;
                        } else {
                            $zxProdElement->title = str_replace('_', ' ', ucfirst(ucfirst($info['filename'])));
                        }

                        $zxProdElement->structureName = $zxProdElement->title;
                        $zxProdElement->party = $structureElement->party;
                        $zxProdElement->compo = $structureElement->compo;
                        $zxProdElement->partyplace = $structureElement->partyplace;
                        $zxProdElement->groups = $structureElement->groups;
                        $zxProdElement->categories = $structureElement->categories;
                        $zxProdElement->year = $structureElement->year;
                        $zxProdElement->description = $structureElement->description;
                        $zxProdElement->denyVoting = $structureElement->denyVoting;
                        $zxProdElement->denyComments = $structureElement->denyComments;
                        $zxProdElement->legalStatus = $structureElement->legalStatus;

                        $zxProdElement->dateAdded = $zxProdElement->dateCreated;
                        $zxProdElement->userId = $this->getService('user')->id;

                        $zxProdElement->checkLinks('categories', 'zxProdCategory');

                        $zxProdElement->renewPartyLink();
                        $zxProdElement->updateTagsInfo();
                        $zxProdElement->updateYear();

                        $zxProdElement->persistElementData();
                        $zxProdElement->logCreation();

                        $linksManager->unLinkElements($structureElement->id, $zxProdElement->getId(), 'structure');
                        $linksManager->linkElements($prodsCatalogueId, $zxProdElement->getId());

                        $privilegesManager->setPrivilege(
                            $user->id,
                            $zxProdElement->getId(),
                            'zxProd',
                            'showPublicForm',
                            'allow'
                        );
                        $privilegesManager->setPrivilege(
                            $user->id,
                            $zxProdElement->getId(),
                            'zxProd',
                            'publicReceive',
                            'allow'
                        );
                        $privilegesManager->setPrivilege(
                            $user->id,
                            $zxProdElement->getId(),
                            'zxProd',
                            'publicDelete',
                            'allow'
                        );
                        $privilegesManager->setPrivilege(
                            $user->id,
                            $zxProdElement->getId(),
                            'zxProd',
                            'deleteFile',
                            'allow'
                        );
                        $privilegesManager->setPrivilege(
                            $user->id,
                            $zxProdElement->getId(),
                            'zxProd',
                            'deleteAuthor',
                            'allow'
                        );
                        $privilegesManager->setPrivilege(
                            $user->id,
                            $zxProdElement->getId(),
                            'zxProd',
                            'submitTags',
                            'allow'
                        );
                        $privilegesManager->setPrivilege(
                            $user->id,
                            $zxProdElement->getId(),
                            'zxProd',
                            'receiveFiles',
                            'allow'
                        );

                        $privilegesManager->setPrivilege(
                            $user->id,
                            $zxProdElement->getId(),
                            'zxRelease',
                            'showPublicForm',
                            'allow'
                        );
                        $privilegesManager->setPrivilege(
                            $user->id,
                            $zxProdElement->getId(),
                            'zxRelease',
                            'publicReceive',
                            'allow'
                        );
                        $privilegesManager->setPrivilege(
                            $user->id,
                            $zxProdElement->getId(),
                            'zxRelease',
                            'publicDelete',
                            'allow'
                        );
                        $privilegesManager->setPrivilege(
                            $user->id,
                            $zxProdElement->getId(),
                            'zxRelease',
                            'deleteFile',
                            'allow'
                        );
                        $privilegesManager->setPrivilege(
                            $user->id,
                            $zxProdElement->getId(),
                            'zxRelease',
                            'deleteAuthor',
                            'allow'
                        );
                        $privilegesManager->setPrivilege(
                            $user->id,
                            $zxProdElement->getId(),
                            'zxRelease',
                            'submitTags',
                            'allow'
                        );

//                        $privilegesManager->setPrivilege($user->id, $zxProdElement->getId(), 'file', 'showForm', 'allow');
                        $privilegesManager->setPrivilege($user->id, $zxProdElement->getId(), 'file', 'delete', 'allow');
                        $privilegesManager->setPrivilege(
                            $user->id,
                            $zxProdElement->getId(),
                            'file',
                            'receive',
                            'allow'
                        );

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
                        }

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
            'file',
        ];
    }
}

