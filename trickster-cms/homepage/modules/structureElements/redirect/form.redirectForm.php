<?php

class RedirectFormStructure extends ElementForm
{
    protected $formClass = 'redirect_form';
    protected $structure = [
        'sourceUrl' => [
            'type' => 'input.text',
        ],
        'partialMatch' => [
            'type' => 'input.checkbox',
        ],
        'destinationElementId' => [

        ],
        'destinationUrl' => [
            'type' => 'input.text',
        ],
    ];


    protected function getSearchTypes()
    {
        return $this->element->getSearchTypesString('public');
    }

    public function getFormComponents()
    {
        $structure = [
            'type' => 'ajaxsearch',
            'class' => 'redirect_searchinput',
            'method' => 'getDestinationElement',
            'types' => $this->getSearchTypes(),
        ];
        $this->structure['destinationElementId'] = $structure;
        return $this->structure;
    }
}