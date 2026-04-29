<?php

use App\Users\CurrentUserService;

class pasteElementsRoot extends structureElementAction
{
    /**
     * @param rootElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $currentUserService = $this->getService(CurrentUserService::class);
        $user = $currentUserService->getCurrentUser();
        $structureElement->executeAction('showFullList');
        if ($contentType = $controller->getParameter('view')) {
            $renderer = $this->getService(renderer::class);
            $renderer->assign('contentType', $contentType);
            $structureManager->setNewElementLinkType($contentType);
        }
        if ($navigateId = $controller->getParameter('navigateId')) {
            if ($navigatedElement = $structureManager->getElementById($navigateId)) {
                $targetUrl = $navigatedElement->URL;
                if ($contentType) {
                    $targetUrl .= 'view:' . $contentType . '/';
                }

                if (($copyInformation = $user->getStorageAttribute('copyInformation')) && !empty($copyInformation['elementsToCopy'])) {
                    if ($copyData = $structureManager->copyElements($copyInformation['elementsToCopy'], $navigateId, [
                        'structure',
                        'subArticle',
                        'headerContent',
                        'leftColumn',
                        'rightColumn',
                        'bottomMenu',
                    ], $contentType)
                    ) {
                        if ($navigateId == $copyInformation['sourceId']) {
                            $this->renameCopies($copyInformation['elementsToCopy'], $copyData);
                        }
                    }
                    $controller->redirect($targetUrl);
                } elseif (($moveInformation = $user->getStorageAttribute('moveInformation')) && !empty($moveInformation['elementsToMove'])) {
                    if ($structureManager->moveElements($moveInformation['elementsToMove'], $navigateId, [
                        'structure',
                        'subArticle',
                        'headerContent',
                        'leftColumn',
                        'rightColumn',
                        'bottomMenu',
                    ])
                    ) {
                        $controller->redirect($targetUrl);
                    }
                }
            }
        }
    }

    protected function renameCopies($topLevel, $copiesData)
    {
        $structureManager = $this->getService('structureManager');
        foreach ($topLevel as &$originalElementId) {
            if (isset($copiesData[$originalElementId])) {
                $renamedElementId = $copiesData[$originalElementId];
                if ($renamedElement = $structureManager->getElementById($renamedElementId)) {
                    if ($renamedElement->title) {
                        foreach ($renamedElement->getLanguagesList() as $languageId) {
                            $renamedElement->setValue("title", $renamedElement->getLanguageValue('title', $languageId) . ' copy', $languageId);
                        }
                        $renamedElement->persistElementData();
                    }
                }
            }
        }
    }
}




