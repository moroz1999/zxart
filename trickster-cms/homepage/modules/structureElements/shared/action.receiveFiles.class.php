<?php

use App\Paths\PathsManager;
use App\Users\CurrentUserService;

/**
 * Class receiveFilesShared
 *
 * @property FilesElementTrait $structureElement
 */
class receiveFilesShared extends structureElementAction
{
    protected $loggable = true;

    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $propertyNames = $structureElement->getFileSelectorPropertyNames();
        foreach ($propertyNames as $propertyName) {
            if ($filesInfo = $structureElement->$propertyName) {
                $isPrivilegesSettingRequired = $structureElement->isPrivilegesSettingRequired();
                $pathsManager = $this->getService(PathsManager::class);
                $uploadsPath = $pathsManager->getPath('uploads');
                $cachePath = $pathsManager->getPath('uploadsCache');
                $privilegesManager = $this->getService(privilegesManager::class);
                $currentUserService = $this->getService(CurrentUserService::class);
                $user = $currentUserService->getCurrentUser();

                foreach ($filesInfo as &$fileInfo) {
                    $temporaryFile = $cachePath . basename($fileInfo['tmp_name']);
                    if (is_file($temporaryFile)) {
                        if ($fileElement = $structureManager->createElement(
                            'file',
                            'showForm',
                            $structureElement->getFilesParentElementId(),
                            false,
                            $structureElement->getConnectedFileType($propertyName)
                        )
                        ) {
                            if ($structureElement instanceof StructureElementUploadedFilesPathInterface) {
                                $folder = $structureElement->getUploadedFilesPath();
                            } else {
                                $folder = $uploadsPath;
                            }
                            $originalFileName = $fileInfo['name'];

                            $info = pathinfo($originalFileName);
                            $fileElement->title = str_replace('_', ' ', ucfirst(ucfirst($info['filename'])));
                            $fileElement->file = $fileElement->getPersistedId();
                            $fileElement->fileName = $originalFileName;

                            $fileElement->persistElementData();

                            copy($temporaryFile, $folder . $fileElement->file);
                            unlink($temporaryFile);
                            if ($isPrivilegesSettingRequired) {
                                $privilegesManager->setPrivilege($user->id, $structureElement->getId(), 'file', 'delete', 'allow');
                            }
                        }
                    }
                }
            }
        }
        if ($url = $structureElement->getFileUploadSuccessUrl()) {
            $controller->redirect($url);
        }
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = $this->structureElement->getFileSelectorPropertyNames();
    }
}





