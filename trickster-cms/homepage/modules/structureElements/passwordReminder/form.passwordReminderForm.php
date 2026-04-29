<?php

class PasswordReminderFormStructure extends ElementForm
{
    protected $structure = [
        'title' => [
            'type' => 'input.text',
        ],
        'content' => [
            'type' => 'input.html',
        ],
        'successMessage' => [
            'type' => 'input.html',
        ],
        'failMessage' => [
            'type' => 'input.html',
        ],
    ];

}