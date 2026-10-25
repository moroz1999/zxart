<?php

use App\Paths\PathsManager;
use App\Users\CurrentUserService;
use ZxArt\Forms\StagedUploadsCleaner;
use ZxArt\Prods\Services\ProdCreator;
use ZxArt\Prods\Services\ProdFieldsDtoFactory;
use ZxArt\Releases\Dto\ReleaseFieldsDto;
use ZxArt\Releases\Services\ReleaseCreator;
use ZxArt\Shared\EntityType;

class batchUploadZxProdsUploadForm extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param zxProdsUploadFormElement $structureElement
     */
    #[Override]
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $firstProd = null;
        if ($filesInfo = $structureElement->file) {
            $linksManager = $this->getService(linksManager::class);
            $currentUserService = $this->getService(CurrentUserService::class);
            $user = $currentUserService->getCurrentUser();
            $prodCreator = $this->getService(ProdCreator::class);
            $prodFieldsDtoFactory = $this->getService(ProdFieldsDtoFactory::class);
            $releaseCreator = $this->getService(ReleaseCreator::class);
            $cachePath = $this->getService(PathsManager::class)->getPath('uploadsCache');

            foreach ($filesInfo as $fileInfo) {
                $originalFileName = $fileInfo['name'];
                $info = pathinfo($originalFileName);
                $prodTitle = (string)$structureElement->getValue('prodTitle');
                if ($prodTitle === '') {
                    $prodTitle = str_replace('_', ' ', ucfirst($info['filename']));
                }
                $fields = $prodFieldsDtoFactory->fromSubmittedForm(
                    $structureElement,
                    $this->expectedFields,
                    $prodTitle,
                    altTitleField: 'prodAltTitle',
                );
                $zxProdElement = $prodCreator->create($fields);
                if (!$firstProd) {
                    $firstProd = $zxProdElement;
                }

                $zxProdElement->addAuthor = $structureElement->addAuthor;
                $zxProdElement->addAuthorRole = $structureElement->addAuthorRole;
                $zxProdElement->connectedFile = $structureElement->connectedFile;
                $zxProdElement->mapFilesSelector = $structureElement->mapFilesSelector;
                $zxProdElement->persistAuthorship(EntityType::Prod);
                $zxProdElement->executeAction('receiveFiles');

                $linksManager->unLinkElements($structureElement->id, $zxProdElement->getPersistedId(), 'structure');

                if ($fileInfo['tmp_name']) {
                    $zxReleaseElement = $releaseCreator->create(
                        new ReleaseFieldsDto(title: $prodTitle, prodId: $zxProdElement->getPersistedId()),
                    );
                    $zxReleaseElement->file = (string)$zxReleaseElement->getPersistedId();
                    $zxReleaseElement->fileName = $fileInfo['name'];
                    $temporaryFile = $cachePath . basename($fileInfo['tmp_name']);
                    copy(
                        $temporaryFile,
                        $this->getService(PathsManager::class)->getPath('releases') . $zxReleaseElement->file
                    );
                    unlink($temporaryFile);

                    $zxReleaseElement->persistElementData();
                    $zxReleaseElement->updateFileStructure();
                }
            }
            // the productions all read the same staged screenshots and maps, so
            // receiving them left the uploads in place; the batch is done with
            // them now
            if ($firstProd instanceof zxProdElement) {
                $this->getService(StagedUploadsCleaner::class)->clearElementUploads($firstProd);
            }
            $user->refreshPrivileges();
        }
        if (!$firstProd instanceof zxProdElement) {
            throw new RuntimeException('At least one software file is required');
        }
        $this->respondFormSaved($controller, $firstProd);
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
            'htmlDescription',
            'htmlInstructions',
            'instructions',
            'categories',
            'publishers',
            'groups',
            'denyVoting',
            'denyComments',
            'legalStatus',
            'externalLink',
            'language',
            'hardwareRequired',
            'file',
            'youtubeId',
            'tags',
            'addAuthor',
            'addAuthorRole',
            'connectedFile',
            'mapFilesSelector',
        ];
    }
}




