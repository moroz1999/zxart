<?php

class moveScreenshotShared extends structureElementAction
{
    protected $loggable = true;
    private string $privilegeName = 'publicReceive';

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param zxProdElement|zxReleaseElement $structureElement
     *
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $direction = $controller->getParameter('direction');
        $fileId = (int)$controller->getParameter('fileId');
        $linkType = $controller->getParameter('linkType');
        if ($linkType === '' || $fileId === 0 || !in_array($direction, ['right', 'left'])) {
            return;
        }

        $linksManager = $this->getService('linksManager');
        $links = $linksManager->getElementsLinks($structureElement->getId(), $linkType, 'parent');

        $currentLink = null;
        foreach ($links as $link) {
            if ($link->childStructureId === $fileId) {
                $currentLink = $link;
                break;
            }
        }

        if ($currentLink === null) {
            return;
        }

        usort($links, static fn($a, $b) => $a->position <=> $b->position);

        $swapLink = null;
        foreach ($links as $index => $link) {
            if ($link->id === $currentLink->id) {
                if ($direction === 'right' && isset($links[$index + 1])) {
                    $swapLink = $links[$index + 1];
                } elseif ($direction === 'left' && isset($links[$index - 1])) {
                    $swapLink = $links[$index - 1];
                }
                break;
            }
        }

        if ($swapLink === null) {
            return;
        }

        $currentPosition = $currentLink->position;
        $currentLink->position = $swapLink->position;
        $swapLink->position = $currentPosition;

        $currentLink->persist();
        $swapLink->persist();

        $structureManager->clearElementCache($structureElement->getId());

        $controller->redirect($structureElement->URL);
    }

    public function getPrivilegeName()
    {
        return $this->privilegeName;
    }
}


