<?php

use App\Users\CurrentUserService;
use ZxArt\Releases\Services\ReleaseCreator;
use ZxArt\Releases\Services\ReleaseFieldsDtoFactory;
use ZxArt\Shared\EntityType;

class publicAddZxRelease extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param zxReleaseElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            if (!is_null($structureElement->getDataChunk("file")->originalName)) {
                $structureElement->file = (string)$structureElement->getPersistedId();
                $structureElement->fileName = $structureElement->getDataChunk("file")->originalName;
            }
            if (!$structureElement->title) {
                $info = pathinfo($structureElement->fileName);
                $structureElement->title = str_replace('_', ' ', ucfirst(ucfirst($info['filename'])));
            }
            $fields = $this->getService(ReleaseFieldsDtoFactory::class)
                ->fromSubmittedForm($structureElement, $this->expectedFields);
            $this->getService(ReleaseCreator::class)->completeCreation($structureElement, $fields);

            $structureElement->persistAuthorship(EntityType::Release);
            $structureElement->persistImportOrigins();

            $structureElement->executeAction('receiveFiles');

            $user = $this->getService(CurrentUserService::class)->getCurrentUser();
            $user->refreshPrivileges();

            $structureElement->updateFileStructure();
            $this->respondFormSaved($controller, $structureElement); return;
        }
        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields): void
    {
        $expectedFields = [
            'title',
            'year',
            'version',
            'description',
            'file',
            'denyVoting',
            'denyComments',
            'releaseType',
            'releaseFormat',
            'language',
            'hardwareRequired',
            'addAuthor',
            'addAuthorRole',
            'publishers',
            'zxProd',
            'importOrigins',
        ];
    }

    public function setValidators(&$validators): void
    {
        $validators['zxProd'][] = 'notEmpty';
    }
}




