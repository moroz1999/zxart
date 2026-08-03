<?php

use ZxArt\Screenshots\ScreenshotFormat;

class uploadScreenshotZxProd extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param zxProdElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $format = ScreenshotFormat::tryFrom((string)$controller->getParameter('format'));
        $data = file_get_contents('php://input');
        if ($format === null || $data === false || strlen($data) !== $format->getSize()) {
            return;
        }

        $fileElement = $structureManager->createElement(
            'file',
            'showForm',
            $structureElement->getFilesParentElementId(),
            false,
            $structureElement->getConnectedFileType('connectedFile')
        );
        if (!$fileElement instanceof fileElement) {
            return;
        }

        $fileElement->title = $structureElement->title;
        $fileElement->file = $fileElement->getPersistedId();
        $fileElement->fileName = $fileElement->getPersistedId() . '.' . $format->getFileExtension();
        $fileElement->persistElementData();

        file_put_contents($structureElement->getUploadedFilesPath() . $fileElement->file, $data);
    }
}
