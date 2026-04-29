<?php

class showFormUser extends structureElementAction
{
    /**
     * @param userElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final) {
            $linksCollection = persistableCollection::getInstance('structure_links');
            $searchFields = ['childStructureId' => $structureElement->id, 'type' => 'userRelation'];
            $userLinks = $linksCollection->load($searchFields);

            $compiledUserLinks = [];
            foreach ($userLinks as &$userLink) {
                $groupId = $userLink->parentStructureId;
                $compiledUserLinks[$groupId] = $userLink;
            }

            if ($userGroupsElement = $structureManager->getElementByMarker('userGroups')) {
                $userGroups = $structureManager->getElementsChildren($userGroupsElement->id);
                $structureElement->userGroupsList = [];
                foreach ($userGroups as &$group) {
                    $userItem['id'] = $group->id;
                    $userItem['title'] = $group->getTitle();
                    if (isset($compiledUserLinks[$group->id])) {
                        $userItem['select'] = true;
                    } else {
                        $userItem['select'] = false;
                    }
                    $structureElement->userGroupsList[] = $userItem;
                }
            }

            $structureElement->setTemplate('shared.content.tpl');
            $renderer = $this->getService(renderer::class);
            $renderer->assign('contentSubTemplate', 'component.form.tpl');
            $renderer->assign('form', $structureElement->getForm('form'));
        }
    }
}