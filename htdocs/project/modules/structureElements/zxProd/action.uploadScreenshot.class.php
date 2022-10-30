<?php

class uploadScreenshotZxProd extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param zxProdElement $structureElement
     * @return mixed|void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $data = file_get_contents('php://input');
        $propertyName = 'connectedFile';
        if ($data && strlen($data) === 6912) {
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
                }
                $this->logError('content length: ' . strlen($data));


                $fileElement->title = $structureElement->title;
                $fileElement->file = $fileElement->getId();
                $fileElement->fileName = $fileElement->getId() . '.scr';

                $fileElement->persistElementData();
                file_put_contents($folder . $fileElement->file, $data);
            }
        }
    }
}