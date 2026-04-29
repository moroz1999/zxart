<?php

use App\Users\CurrentUserService;

class showSearchLog extends structureElementAction
{
    protected $actionsLogData;

    /**
     * @param searchLogElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->requested) {
            $filterNames = [
                "periodStart",
                "periodEnd",
                "phrase",
                "bNotClicked",
                "bZeroResultsOnly",
            ];
            $filters = $this->getFilters($structureElement->getFormData(), $filterNames);
            $structureElement->actionsLogData = $structureElement->getLogData($filters);

            if ($structureElement->final) {
                $structureElement->setTemplate('shared.content.tpl');
                $renderer = $this->getService(renderer::class);
                $renderer->assign('contentSubTemplate', 'searchLog.list.tpl');
                $renderer->assign('logData', $structureElement->actionsLogData);
                $renderer->assign('pager', $structureElement->pager);
            }
        }
    }

    protected function getFilters($formData, &$filterNames)
    {
        $filterData = [];
        $currentUserService = $this->getService(CurrentUserService::class);
        $user = $currentUserService->getCurrentUser();

        foreach ($filterNames as &$filterName) {
            if (isset($formData[$filterName])) {
                $formData[$filterName] = trim($formData[$filterName]);
                $this->structureElement->$filterName = $formData[$filterName];
                $user->setStorageAttribute("searchLog_$filterName", $formData[$filterName]);
                $filterData[$filterName] = $formData[$filterName];
            } else {
                if ($filterData[$filterName] = $user->getStorageAttribute("searchLog_$filterName")) {
                    $this->structureElement->$filterName = $filterData[$filterName];
                }
            }
        }
        return $filterData;
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'phrase',
            'periodStart',
            'periodEnd',
            'bZeroResultsOnly',
            'bNotClicked',
        ];
    }
}





