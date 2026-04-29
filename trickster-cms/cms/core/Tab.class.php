<?php

class Tab
{
    /**
     * @var structureElement $structureElement
     */
    protected $structureElement;
    protected $name;
    protected $action;
    protected $view;
    protected $label;
    protected $icon;
    protected $url;
    protected $isActive;

    /**
     * @return mixed
     */
    public function getAction()
    {
        if (!empty($this->action)) {
            return $this->action;
        }
        return $this->name;
    }

    public function __construct($data = [])
    {
        $this->loadData($data);
        $this->init();
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        if ($this->isActive === null) {
            $this->isActive = $this->structureElement->actionName == $this->getAction();
        }
        return $this->isActive;
    }

    /**
     * @param mixed $structureElement
     */
    public function setStructureElement($structureElement)
    {
        $this->structureElement = $structureElement;
    }

    protected function init()
    {
        return true;
    }

    public function loadData($data = [])
    {
        foreach ($data as $optionName => $optionValue) {
            $this->$optionName = $optionValue;
        }
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getViewName()
    {
        return $this->view;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function getIcon()
    {
        return $this->icon;
    }

    public function getUrl()
    {
        if ($this->url === null) {
            if ($this->getViewName() !== null) {
                $this->url = $this->structureElement->getUrl($this->getAction()) . 'view:' . $this->getViewName() . '/';
            } else {
                $this->url = $this->structureElement->getUrl($this->getAction());
            }
        }
        return $this->url;
    }

    /**
     * @param mixed $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }
}