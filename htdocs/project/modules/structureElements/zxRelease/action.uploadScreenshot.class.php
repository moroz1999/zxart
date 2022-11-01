<?php

class uploadScreenshotZxRelease extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param zxReleaseElement $structureElement
     * @return mixed|void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $data = file_get_contents('php://input');
        $propertyName = 'screenshotsSelector';
        if ($data && (strlen($data) === 6912 || strlen($data) === 6912 * 2)) {
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

                $fileElement->title = $structureElement->title;
                $fileElement->file = $fileElement->getId();
                if (strlen($data) === 6912){
                    $fileElement->fileName = $fileElement->getId() . '.scr';
                } else {
                    $fileElement->fileName = $fileElement->getId() . '.img';
                }

                $fileElement->persistElementData();
                file_put_contents($folder . $fileElement->file, $data);
            }
        }
    }
}