<?php

class uploadScreenshotZxProd extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param zxProdElement $structureElement
     *
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $format = $controller->getParameter('format');
        $data = file_get_contents('php://input');
        $propertyName = 'connectedFile';
        if (!$data || !$format) {
            return;
        }

        $formats = [
            'standard' => 6912,
            'gigascreen' => 6912 * 2,
            's80' => 768,
            's81' => 768,
        ];

        if (isset($formats[$format]) && strlen($data) === $formats[$format]) {
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

                switch ($format) {
                    case 'standard':
                        $fileElement->fileName = $fileElement->getId() . '.scr';
                        break;
                    case 'gigascreen':
                        $fileElement->fileName = $fileElement->getId() . '.img';
                        break;
                    case 's80':
                        $fileElement->fileName = $fileElement->getId() . '.s80';
                        break;
                    case 's81':
                        $fileElement->fileName = $fileElement->getId() . '.s81';
                        break;
                    default:
                        $fileElement->fileName = $fileElement->getId();
                        break;
                }

                $fileElement->persistElementData();
                file_put_contents($folder . $fileElement->file, $data);
            }
        }
    }
}