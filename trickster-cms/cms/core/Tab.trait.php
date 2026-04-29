<?php

trait TabTrait
{
    /**
     * @return bool
     */
    public function isActive()
    {
        if ($this->isActive === null) {
            $controller = controller::getInstance();
            $viewName = $controller->getParameter('view');
            $this->isActive = $this->structureElement->actionName == $this->getAction() && $viewName == $this->getViewName();
        }
        return $this->isActive;
    }
}