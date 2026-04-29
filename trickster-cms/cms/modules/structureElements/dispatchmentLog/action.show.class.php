<?php

use App\Users\CurrentUserService;

class showDispatchmentLog extends structureElementAction
{
    /**
     * @param dispatchmentLogElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->requested) {
            $filters = $this->getFilters($structureElement->getFormData());
            $structureElement->actionsLogData = $structureElement->getLogData($filters);

            if ($structureElement->final) {
                $structureElement->setTemplate('shared.content.tpl');
                $renderer = $this->getService(renderer::class);
                $renderer->assign('contentSubTemplate', 'dispatchmentLog.tpl');
                $renderer->assign('logData', $structureElement->actionsLogData);
                $renderer->assign('pager', $structureElement->pager);
            }
        }
    }

    protected function getFilters($formData)
    {
        $filter = [];
        $currentUserService = $this->getService(CurrentUserService::class);
        $user = $currentUserService->getCurrentUser();

        if (isset($formData["periodStart"])) {
            $filter["periodStart"] = $this->getTimeStamp($formData["periodStart"]);
            $user->setStorageAttribute("dispatchmentLog_periodStart", $filter["periodStart"]);
        } else {
            if ($filter["periodStart"] = $user->getStorageAttribute("dispatchmentLog_periodStart")) {
                $this->structureElement->periodStart = date("d.m.Y", $filter["periodStart"]);
            }
        }

        if (isset($formData["periodEnd"])) {
            $filter["periodEnd"] = $this->getTimeStamp($formData["periodEnd"]);
            $user->setStorageAttribute("dispatchmentLog_periodEnd", $filter["periodEnd"]);
        } else {
            if ($filter["periodEnd"] = $user->getStorageAttribute("dispatchmentLog_periodEnd")) {
                $this->structureElement->periodEnd = date("d.m.Y", $filter["periodEnd"]);
            }
        }

        if (isset($formData["dispatchmentId"])) {
            $filter["dispatchmentId"] = $formData["dispatchmentId"];
            $user->setStorageAttribute("dispatchmentLog_dispatchmentId", $filter["dispatchmentId"]);
        } else {
            if ($filter["dispatchmentId"] = $user->getStorageAttribute("dispatchmentId")) {
                $this->structureElement->dispatchmentId = $filter["dispatchmentId"];
            }
        }
        if (isset($formData["email"])) {
            $filter["email"] = trim($formData["email"]);
            $user->setStorageAttribute("dispatchmentLog_email", $filter["email"]);
        } else {
            if ($filter["email"] = $user->getStorageAttribute("email")) {
                $this->structureElement->email = $filter["email"];
            }
        }
        return $filter;
    }

    protected function getTimeStamp(&$dateString)
    {
        $dateParts = explode(".", $dateString);
        if (count($dateParts) != 3) {
            return false;
        }
        $stamp = mktime(0, 0, 0, $dateParts[1], $dateParts[0], $dateParts[2]);
        return $stamp;
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'email',
            'dispatchmentId',
            'periodStart',
            'periodEnd',
        ];
    }
}



