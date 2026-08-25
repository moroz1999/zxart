<?php

class UserFormStructure extends ElementForm
{
    protected $structure = [
        'userName' => [
            'type' => 'input.text',
        ],
        'password' => [
            'type' => 'input.password',
        ],
        'email' => [
            'type' => 'input.text',
        ],
        'subscribe' => [
            'type' => 'input.checkbox',
        ],
        'userGroups' => [
            'type' => 'select.universal_options_multiple',
            'property' => 'userGroupsList',

        ],
    ];

    public function getControls()
    {
        if (empty($this->controls)) {
            $this->controls = [
                'save' => [
                    'class' => 'success_button',
                    'type' => 'submit',
                ],
                'ban' => [
                    'class' => 'warning_button',
                    'action' => 'ban',
                    'icon' => 'delete',
                    'confirmation' => 'message.deleteelementconfirmation',
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

}