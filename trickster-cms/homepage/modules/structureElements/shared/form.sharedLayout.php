<?php

class SharedLayoutStructure extends ElementForm
{

    public function getTranslationGroup()
    {
        return 'layout';
    }

    public function getFormComponents()
    {
        $structure = [];
        foreach ($this->element->getLayoutTypes() as $type) {
            $structure[$type] = [
                'type' => 'input.layouts_selection',
                'defaultLayout' => $this->element->getDefaultLayout($type),
                'layouts' => $this->element->getLayoutsSelection($type),
            ];
        }
        return $structure;
    }
}