<?php

class showDashboardTab extends Tab
{
    protected function init()
    {
        $this->action = 'showFullList';
        $this->view = 'dashboard';
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        if ($this->isActive === null) {
            $controller = controller::getInstance();
            $viewName = $controller->getParameter('view');
            $this->isActive = $this->structureElement->actionName == $this->getAction() && ($viewName == $this->getViewName() || !$viewName);
        }
        return $this->isActive;
    }
}