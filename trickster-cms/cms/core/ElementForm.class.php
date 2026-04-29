<?php

abstract class ElementForm implements DependencyInjectionContextInterface
{
    use DependencyInjectionContextTrait;
    protected $containerClass;
    protected $formClass = "";
    protected $formEnctype = 'multipart/form-data';
    protected $formMethod = 'post';
    protected $formAction;
    protected $controlsLayout = 'component.controls.tpl';
    protected $structure = [];
    protected $preset = '';
    protected $customComponent = '';
    protected $additionalContent;
    protected $additionalContentTable;
    protected $additionalControls = true;
    protected $controls = [];
    /**
     * @var structureElement $element
     */
    protected $element;

    /**
     * @param structureElement $element
     */
    public function setElement(structureElement $element)
    {
        $this->element = $element;
    }

    /**
     * @return structureElement
     */
    public function getElement(): structureElement
    {
        if(!empty($this->element)) {
            return $this->element;
        }
        return false;
    }


    public function getPreset()
    {
        return $this->preset;
    }

    public function getControlsLayout()
    {
        return $this->controlsLayout;
    }

    public function setControlsLayout($layout)
    {
        $this->controlsLayout = $layout;
    }

    public function getFormClass()
    {
        return $this->formClass;
    }

    public function getFormMethod()
    {
        return $this->formMethod;
    }

    public function getFormEnctype()
    {
        return $this->formEnctype;
    }

    public function getFormAction()
    {
        return $this->formAction;
    }

    public function getFormComponents()
    {
        return $this->structure;
    }

    public function setFormClass($formClass)
    {
        $this->formClass = $formClass;
    }

    public function setFormMethod($formMethod)
    {
        $this->formMethod = $formMethod;
    }

    public function setFormEnctype($formEnctype)
    {
        $this->formEnctype = $formEnctype;
    }

    public function setFormAction($formAction)
    {
        $this->formAction = $formAction;
    }

    public function setStructure($structure)
    {
        $this->structure = $structure;
    }

    public function callElementMethod($methodName, $param = "")
    {
        if (method_exists($this->element, $methodName)) {
            return $this->element->$methodName($param);
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getAdditionalContent()
    {
        return $this->additionalContent;
    }

    public function getElementProperty($property)
    {
        return $this->element->$property;
    }

    public function getElementOptions($options)
    {
        return $this->$options;
    }

    public function getTranslationGroup()
    {
        return strtolower($this->element->structureType);
    }

    /**
     * @return string
     */
    public function getCustomComponent()
    {
        return $this->customComponent;
    }

    public function getControls()
    {
        if (empty($this->controls)) {
            $this->controls = [
                'save' => [
                    'class' => 'success_button',
                    'type' => 'submit',
                ],
                'delete' => [
                    'class' => 'warning_button',
                    'action' => 'delete',
                    'icon' => 'delete',
                    'confirmation' => 'message.deleteelementconfirmation',
                ],
            ];
        }
        return $this->controls;
    }

    /**
     * @return bool
     */
    public function getAdditionalControls()
    {
        return $this->additionalControls;
    }

    /**
     * @return mixed
     */
    public function getContainerClass()
    {
        return $this->containerClass;
    }

    /**
     * @return mixed
     */
    public function getAdditionalContentTable()
    {
        return $this->additionalContentTable;
    }

    /**
     * @return bool
     */
    public function formObjectEmpty()
    {
        // empty(get_object_vars($form)
        return empty((array)$this);
    }
}