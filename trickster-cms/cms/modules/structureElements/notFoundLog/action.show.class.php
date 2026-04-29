<?php

use App\Users\CurrentUserService;

class showNotFoundLog extends structureElementAction
{
    protected $notFoundLogData;

    /**
     * @param notFoundLogElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->requested) {
            $formData = $structureElement->getFormData();
            $filterNames = [
                "errorUrl",
                "redirectUrl",
                "ignoreRedirected",
            ];
            $filters = $this->getFilters($formData, $filterNames);
            $structureElement->logData = $structureElement->getLogData($filters);

            if ($structureElement->final) {
                $structureElement->setTemplate('shared.content.tpl');
                $renderer = $this->getService(renderer::class);
                $renderer->assign('contentSubTemplate', 'notFoundLog.tpl');
                $renderer->assign('logData', $structureElement->logData);
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
                $user->setStorageAttribute("notFoundLog_$filterName", $formData[$filterName]);
                $filterData[$filterName] = $formData[$filterName];
            } else {
                if ($filterData[$filterName] = $user->getStorageAttribute("notFoundLog_$filterName")) {
                    $this->structureElement->$filterName = $filterData[$filterName];
                }
            }
        }
        return $filterData;
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'errorUrl',
            'ignoreRedirected',
        ];
    }
}





