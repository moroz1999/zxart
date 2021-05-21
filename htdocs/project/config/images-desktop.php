<?php return [
    'authorPhoto' => [
        'filters' => [
            [
                'fit',
                'width=48, height=48',
            ],
            [
                'crop',
                'width=48, height=48, soft=1',
            ],
            [
                'sharpen',
                'amount=50',
            ],
            [
                'aspectedResize',
                'width=192, height=192',
            ],
            [
                'dither',
                'method=bayer,offset=0.7',
            ],
        ],
        'format' => [
            null,
            'png',
        ],
    ],
    'groupImage' => [
        'filters' => [
            [
                'reduce',
                'width=192, height=192',
            ],
        ],
        'format' => [
            null,
            'png',
        ],
    ],
    'inspiredImage' => [
        'filters' => [
            [
                'reduce',
                'width=500, height=400',
            ],
        ],
        'format' => [
            null,
            'jpg',
        ],
    ],
    'prodImage' => [
        'filters' => [
            [
                'reduce',
                'width=320, height=240',
            ],
        ],
        'format' => [
            null,
            'png',
            '',
            100,
        ],
        'path' => 'releases',
    ],
    'prodListInlay' => [
        'filters' => [
            [
                'reduce',
                'height=400',
            ],
            [
                'crop',
                'width=400, height=400, soft=1',
            ],
        ],
        'format' => [
            null,
            'jpg',
            '',
            100,
        ],
        'path' => 'releases',
    ],
    'prodListImage' => [
        'filters' => [
            [
                'crop',
                'width=256, height=192',
            ],
        ],
        'format' => [
            null,
            'png',
            '',
            100,
        ],
        'path' => 'releases',
    ],
    'inspired2Image' => [
        'filters' => [
            [
                'reduce',
                'width=400, height=300',
            ],
        ],
        'format' => [
            null,
            'jpg',
        ],
    ],
    'linklist' => [
        'filters' => [
            [
                'reduce',
                'width=200, height=200',
            ],
            [
                'crop',
                'width=200, height=200',
            ],
        ],
        'format' => [
            null,
            'png',
        ],
    ],

    'prodMapImage' => [
        'filters' => [
            [
                'reduce',
                'width=300, height=300',
            ],
        ],
        'format' => [
            null,
            'png',
            '',
            '100',
        ],
        'path' => 'releases',
    ],
    'prodImageFullImage' => [
        'filters' => [
            [
                'reduce',
                'width=1920, height=1080',
            ],
        ],
        'format' => [
            null,
            'jpg',
            '',
            100,
        ],
        'path' => 'releases',
    ],
    'prodImageSmallImage' => [
        'filters' => [
            [
                'reduce',
                'width=250, height=120',
            ],
        ],
        'format' => [
            null,
            'jpg',
            '',
            '90',
        ],
        'path' => 'releases',
    ],
    'partyShort' => [
        'filters' => [
            [
                'reduce',
                'width=270, height=200',
            ],
            [
                'crop',
                'width=270, height=200',
            ],
        ],
        'format' => [
            null,
            'png',
        ],
    ],
    'partyDetails' => [
        'filters' => [
            [
                'reduce',
                'width=512, height=300',
            ],
        ],
        'format' => [
            null,
            'png',
        ],
    ],
];