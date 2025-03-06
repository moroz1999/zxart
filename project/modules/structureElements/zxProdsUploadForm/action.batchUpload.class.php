<?php

use ZxArt\Queue\QueueService;
use ZxArt\Queue\QueueType;
use ZxArt\Queue\QueueStatus;
use ZxArt\ZxProdCategories\CategoryIds;

class batchUploadZxProdsUploadForm extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param zxProdsUploadFormElement $structureElement
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $firstProd = null;
        if ($filesInfo = $structureElement->file) {
            $privilegesManager = $this->getService('privilegesManager');
            $linksManager = $this->getService('linksManager');
            $user = $this->getService('user');
            /**
             * @var QueueService $queueService
             */
            $queueService = $this->getService('QueueService');
            $cachePath = $this->getService('PathsManager')->getPath('uploadsCache');
            if (!$structureElement->categories) {
                $structureElement->categories = [CategoryIds::MISC->value];
            }
            $firstCategoryId = $structureElement->categories[0];

            foreach ($filesInfo as $fileInfo) {
                /**
                 * @var zxProdElement $zxProdElement
                 */
                if ($zxProdElement = $structureManager->createElement('zxProd', 'show', $firstCategoryId)) {
                    if (!$firstProd) {
                        $firstProd = $zxProdElement;
                    }
                    $originalFileName = $fileInfo['name'];
                    $info = pathinfo($originalFileName);

                    if ($structureElement->prodTitle) {
                        $zxProdElement->title = $structureElement->prodTitle;
                    } else {
                        $zxProdElement->title = str_replace('_', ' ', ucfirst(ucfirst($info['filename'])));
                    }

                    $zxProdElement->altTitle = $structureElement->prodAltTitle;
                    $zxProdElement->structureName = $zxProdElement->title;
                    $zxProdElement->externalLink = $structureElement->externalLink;
                    $zxProdElement->party = $structureElement->party;
                    $zxProdElement->compo = $structureElement->compo;
                    $zxProdElement->partyplace = $structureElement->partyplace;
                    $zxProdElement->groups = array_map(static fn($id) => $structureManager->getElementById($id), $structureElement->groups);
                    $zxProdElement->categories = $structureElement->categories;
                    $zxProdElement->publishers = array_map(static fn($id) => $structureManager->getElementById($id), $structureElement->publishers);
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

                    $zxProdElement->checkLinks('categories', 'zxProdCategory');

                    $zxProdElement->renewPartyLink();
                    $zxProdElement->updateTagsInfo();
                    $zxProdElement->updateYear();
                    $zxProdElement->persistAuthorship('prod');

                    $zxProdElement->executeAction('receiveFiles');

                    $queueService->updateStatus($structureElement->getId(), QueueType::AI_CATEGORIES_TAGS, QueueStatus::STATUS_SKIP);


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
                    if ($fileInfo['tmp_name']) {
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

                            if ($temporaryFile = $cachePath . basename($fileInfo['tmp_name'])) {
                                $zxReleaseElement->fileName = $fileInfo['name'];
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
            }
            $user->refreshPrivileges();
        }
        $controller->redirect($firstProd->URL);
    }

    public function setExpectedFields(&$expectedFields): void
    {
        $expectedFields = [
            'prodTitle',
            'prodAltTitle',
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

