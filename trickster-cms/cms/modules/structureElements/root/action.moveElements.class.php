<?php

use App\Users\CurrentUserService;

class moveElementsRoot extends structureElementAction
{
    /**
     * @param rootElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $currentUserService = $this->getService(CurrentUserService::class);
        $user = $currentUserService->getCurrentUser();
        $structureElement->executeAction('showFullList');
        $navigationRoot = $structureManager->getElementByMarker($this->getService(ConfigManager::class)
            ->get('main.rootMarkerAdmin'));
        $navigateId = $controller->getParameter('navigateId');
        if ($contentType = $controller->getParameter('view')) {
            $renderer = $this->getService(renderer::class);
            $renderer->assign('contentType', $contentType);
            $structureManager->setNewElementLinkType($contentType);
        }

        if (!$navigateId) {
            $moveInformation = [];

            $moveInformation['elementsToCopy'] = [];
            $moveInformation['elementsToMove'] = [];
            $moveInformation['structureTypes'] = [];
            $elements = $structureElement->elements;
            $sourceId = false;
            if (is_array($elements)) {
                foreach ($elements as $elementID => &$value) {
                    if ($movedElement = $structureManager->getElementById($elementID)) {
                        if ((!$sourceId || !$navigateId) && ($firstParent = $structureManager->getElementsFirstParent($movedElement->id))
                        ) {
                            if (!$sourceId) {
                                $sourceId = $firstParent->id;
                            }
                            if (!$navigateId) {
                                $navigateId = $firstParent->id;
                            }
                        }

                        $moveInformation['elementsToMove'][] = $movedElement->id;
                        $moveInformation['structureTypes'][$movedElement->structureType] = true;
                    }
                }
            }
            $navigateId = $sourceId;
            $moveInformation['sourceId'] = $sourceId;
            $user->setStorageAttribute('copyInformation', false);
            $user->setStorageAttribute('moveInformation', $moveInformation);
        } else {
            $moveInformation = $user->getStorageAttribute('moveInformation');
        }

        if ($moveInformation && count($moveInformation['elementsToMove']) > 0) {
            if ($navigatedElement = $structureManager->getElementById($navigateId)) {
                $structureManager->setCurrentElement($structureElement);
                $structureElement->navigationRoot = $navigationRoot;
                $structureElement->navigationTree = [$navigationRoot];
                $structureElement->destinationElement = $navigatedElement;
                $this->markNavigatedChain($navigationRoot, $navigatedElement);
                $structureElement->pasteAllowed = $this->checkAllowedTypes($navigatedElement, array_keys($moveInformation['structureTypes']));

                $structureElement->setTemplate('shared.move.tpl');
            }
        }
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = ['elements'];
    }

    protected function markNavigatedChain($navigationRoot, $currentElement)
    {
        $structureManager = $this->getService('structureManager');
        $currentElement->navigated = true;
        $structureManager->getElementsChildren($currentElement->id, 'container');
        if ($currentElement->id != $navigationRoot->id) {
            if ($parentElement = $structureManager->getElementsFirstParent($currentElement->id)) {
                $this->markNavigatedChain($navigationRoot, $parentElement);
            }
        }
    }

    protected function checkAllowedTypes(structureElement $destinationElement, array $typesList)
    {
        $result = true;
        $allowedTypes = $destinationElement->getAllowedTypes();
        foreach ($typesList as &$type) {
            if (!in_array($type, $allowedTypes)) {
                $result = false;
                break;
            }
        }
        return $result;
    }
}




