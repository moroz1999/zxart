<?php

class showFullListTab extends Tab
{
    protected function init()
    {
        $this->action = 'showFullList';
        $this->icon = 'icon_list';
    }

    public function isActive()
    {
        if ($this->isActive === null) {
            $controller = controller::getInstance();
            $viewName = $controller->getParameter('view');
            $this->isActive = $this->structureElement->actionName == $this->getAction() && !$viewName;
        }
        return $this->isActive;
    }
}