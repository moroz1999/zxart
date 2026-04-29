<?php return [
    'adminImage' => [
        'filters' => [
            [
                'reduce',
                ['width' => 170, 'height' => 170],
            ],
        ],
        'format' => [
            null,
            'png',
        ],
    ],
    'background' => [
        'filters' => [
            [
                'reduce',
                ['width' => 1920, 'height' => 1920],
            ],
        ],
        'format' => [
            null,
            'jpg',
            '',
            80,
        ],
    ],
    'customBackground' => [
        'filters' => [],
        'format' => [
            null,
            'jpg',
            '',
            80,
        ],
    ],
    'languageFlag' => [
        'filters' => [
            [
                'strictResize',
                ['width' => 28, 'height' => 16],
            ],
        ],
        'format' => [
            null,
            'png',
        ],
    ],
    'logo' => [
        'filters' => [
            [
                'reduce',
                ['width' => 512, 'height' => 512],
            ],
        ],
        'format' => [
            null,
            'png',
        ],
    ],
    'registrationSocialPluginIcon' => [
        'filters' => [
            [
                'reduce',
                ['width' => 48, 'height' => 48],
            ],
        ],
        'format' => [
            null,
            'png',
        ],
    ],
    'loginSocialPluginIcon' => [
        'filters' => [
            [
                'reduce',
                ['width' => 48, 'height' => 48],
            ],
        ],
        'format' => [
            null,
            'png',
        ],
    ],
    'original' => [
        'format' => [
            null,
            'png',
        ],
    ],
];
