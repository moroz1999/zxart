<?php

class exportFeedback extends structureElementAction
{
    /**
     * @param feedbackElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final) {
            $archive = $structureElement->getExportArchive();
            if ($archive !== '') {
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename=feedback_export.zip');
                header('Content-Length: ' . filesize($archive));
                readfile($archive);
            } else {
                header('HTTP/1.0 404 Not Found');
            }
            exit;
        }
        $structureElement->setViewName('form');
    }
}

