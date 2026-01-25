<?php

class showComments extends structureElementAction
{
    protected $actionsLogData;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param commentsElement $structureElement
     * @return mixed|void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($structureElement->requested) {
            $filters = $this->getFilters($structureElement->getFormData());
            $structureElement->comments = $structureElement->getComments($filters);

            if ($structureElement->final) {
                $structureElement->setTemplate('shared.content.tpl');
                $renderer = $this->getService('renderer');
                $renderer->assign('contentSubTemplate', 'comments.list.tpl');
                $renderer->assign('comments', $structureElement->comments);
                $renderer->assign('pager', $structureElement->pager);
            }
        }
    }

    protected function getFilters($formData)
    {
        $filter = [];
        $user = $this->getService('user');

        if (isset($formData["periodStart"])) {
            $filter["periodStart"] = $formData["periodStart"];
            $user->setStorageAttribute("comments_periodStart", $filter["periodStart"]);
        } else {
            if ($filter["periodStart"] = $user->getStorageAttribute("comments_periodStart")) {
                $this->structureElement->periodStart = $filter["periodStart"];
            }
        }

        if (isset($formData["periodEnd"])) {
            $filter["periodEnd"] = $formData["periodEnd"];
            $user->setStorageAttribute("comments_periodEnd", $filter["periodEnd"]);
        } else {
            if ($filter["periodEnd"] = $user->getStorageAttribute("comments_periodEnd")) {
                $this->structureElement->periodEnd = $filter["periodEnd"];
            }
        }

        if (isset($formData["author"])) {
            $filter["author"] = $formData["author"];
            $user->setStorageAttribute("comments_author", $filter["author"]);
        } else {
            if ($filter["author"] = $user->getStorageAttribute("comments_author")) {
                $this->structureElement->author = $filter["author"];
            }
        }
        if (isset($formData["ipAddress"])) {
            $filter["ipAddress"] = $formData["ipAddress"];
            $user->setStorageAttribute("comments_ipAddress", $filter["ipAddress"]);
        } else {
            if ($filter["ipAddress"] = $user->getStorageAttribute("comments_ipAddress")) {
                $this->structureElement->ipAddress = $filter["ipAddress"];
            }
        }
        return $filter;
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'author',
            'email',
            'ipAddress',
            'periodStart',
            'periodEnd',
        ];
    }
}
