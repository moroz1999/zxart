<?php

class CommentFormStructure extends ElementForm
{
    protected $formClass = 'comment_form';
    protected $structure = [
        'userId' => [

        ],
        'author' => [
            'type' => 'input.text',
        ],
        'email' => [
            'type' => 'input.text',
        ],
        'content' => [
            'type' => 'input.html',
        ],
        'ipAddress' => [
            'type' => 'input.text',
        ],
        'approved' => [
            'type' => 'input.checkbox',
        ],
        'replies' => [
            'type' => 'show.comment_replies',
        ],
    ];


    protected function getSearchTypes()
    {
        return $this->element->getSearchTypesString('admin');
    }

    public function getFormComponents()
    {
        $structure = [
            'type' => 'ajaxsearch',
            'class' => 'comment_form_user_select',
            'method' => 'getUserName',
            'types' => $this->getSearchTypes(),
        ];
        $this->structure['userId'] = $structure;
        return $this->structure;
    }
}