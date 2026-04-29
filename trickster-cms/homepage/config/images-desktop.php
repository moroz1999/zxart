<?php return [
    'eventShort' => [
        'filters' => [
            [
                'reduce',
                ['width' => 180],
            ],
            [
                'crop',
                ['width' => 180, 'height' => 180, 'color' => '#ffffff', 'soft' => 1],
            ],
        ],
        'format' => [
            null,
            'jpg',
            '',
            80,
        ],
    ],
    'eventDetails' => [
        'filters' => [
            [
                'reduce',
                ['width' => 400],
            ],
        ],
        'format' => [
            null,
            'jpg',
            '',
            80,
        ],
    ],
    'eventDetailed' => [
        'filters' => [
            [
                'reduce',
                ['width' => 300, 'height' => 300],
            ],
            [
                'crop',
                ['width' => 300, 'height' => 300, 'color' => '#ffffff'],
            ],
        ],
        'format' => [
            null,
            'jpg',
            '',
            80,
        ],
    ],
    'headerGalleryImage' => [
        'filters' => [
            [
                'fit',
                ['width' => 1415, 'height' => 325],
            ],
        ],
        'format' => [
            null,
            'jpg',
            '',
            85,
        ],
    ],
    'headerGalleryImageMobile' => [
        'filters' => [
            [
                'fit',
                ['width' => 1415, 'height' => 325],
            ],
        ],
        'format' => [
            null,
            'jpg',
            '',
            85,
        ],
    ],
    'headerGalleryFullImage' => [
        'filters' => [
            [
                'reduce',
                ['width' => 1920, 'height' => 1080],
            ],
        ],
        'format' => [
            null,
            'jpg',
            null,
            80,
        ],
    ],
    'headerGalleryFullImageMobile' => [
        'filters' => [
            [
                'reduce',
                ['width' => 768, 'height' => 768],
            ],
        ],
        'format' => [
            null,
            'jpg',
            null,
            80,
        ],
    ],
    'galleryShortImage' => [
        'filters' => [
            [
                'fit',
                ['width' => 430, 'height' => 320],
            ],
            [
                'crop',
                ['width' => 430, 'height' => 320, 'color' => '#ffffff'],
            ],
        ],
        'format' => [
            null,
            'jpg',
            '',
            80,
        ],
    ],
    'galleryImage' => [
        'filters' => [
            [
                'reduce',
                ['height' => 600],
            ],
        ],
        'format' => [
            null,
            'jpg',
            '',
            80,
        ],
    ],
    'galleryThumbnailImage' => [
        'filters' => [
            [
                'fit',
                ['width' => 310, 'height' => 240],
            ],
            [
                'crop',
                ['width' => 310, 'height' => 240, 'color' => '#ffffff'],
            ],
        ],
        'format' => [
            null,
            'jpg',
            '',
            80,
        ],
    ],
    'galleryThumbnailUnevenImage' => [
        'filters' => [
            [
                'reduce',
                ['width' => 1000],
            ],
            [
                'crop',
                ['color' => '#ffffff'],
            ],
        ],
        'format' => [
            null,
            'jpg',
            '',
            80,
        ],
    ],
    'gallerySmallThumbnailImage' => [
        'filters' => [
            [
                'fit',
                ['width' => 250, 'height' => 190],
            ],
            [
                'crop',
                ['width' => 250, 'height' => 190, 'color' => '#ffffff'],
            ],
        ],
        'format' => [
            null,
            'jpg',
            '',
            80,
        ],
    ],
    'galleryFullImage' => [
        'filters' => [
            [
                'reduce',
                ['width' => 1920, 'height' => 1080],
            ],
        ],
        'format' => [
            null,
            'jpg',
            null,
            80,
        ],
    ],
    'galleryFullImageMobile' => [
        'filters' => [
            [
                'reduce',
                ['width' => 1920, 'height' => 1080],
            ],
        ],
        'format' => [
            null,
            'jpg',
            null,
            80,
        ],
    ],
    'linklist' => [
        'filters' => [
            [
                'reduce',
                ['width' => 310, 'height' => 310],
            ],
            [
                'crop',
                ['width' => 310, 'height' => 310, 'color' => '#ffffff'],
            ],
        ],
        'format' => [
            null,
            'jpg',
            '',
            80,
        ],
    ],

    'linklistItemThumbnail' => [
        'filters' => [
            [
                'fit',
                ['width' => 353, 'height' => 310],
            ],
            [
                'crop',
                ['width' => 353, 'height' => 310, 'color' => '#ffffff'],
            ],
        ],
        'format' => [
            null,
            'jpg',
            '',
            80,
        ],
    ],
    'linklistItemThumbnailLong' => [
        'filters' => [
            [
                'fill',
                ['width' => 600, 'height' => 720],
            ],
            [
                'crop',
                ['width' => 600, 'height' => 720],
            ],
        ],
        'format' => [
            null,
            'jpg',
            '',
            80,
        ],
    ],
    'linklistItemButton' => [
        'filters' => [
            [
                'reduce',
                ['width' => 128, 'height' => 128],
            ],
        ],
        'format' => [
            null,
            'png',
        ],
    ],
    'linklistItemSimple' => [
        'filters' => [
            [
                'reduce',
                ['width' => 250],
            ],
            [
                'crop',
                ['width' => 250, 'height' => 110, 'color' => '#ffffff'],
            ],
        ],
        'format' => [
            null,
            'jpg',
            '',
            80,
        ],
    ],
    'linklistItemTabbed' => [
        'filters' => [
            [
                'reduce',
                ['height' => 310],
            ],
        ],
        'format' => [
            null,
            'jpg',
            '',
            80,
        ],
    ],
    'linklistItemDetailed' => [
        'filters' => [
            [
                'reduce',
                ['width' => 310, 'height' => 310],
            ],
            [
                'crop',
                ['width' => 310, 'height' => 310, 'color' => '#ffffff'],
            ],
        ],
        'format' => [
            null,
            'jpg',
            '',
            80,
        ],
    ],
    'mapImage' => [
        'filters' => [
            [
                'reduce',
                ['width' => 379, 'height' => 252],
            ],
        ],
        'format' => [
            null,
            'png',
        ],
    ],
    'newsShortImage' => [
        'filters' => [
            [
                'fit',
                ['width' => 256, 'height' => 227],
            ],
            [
                'crop',
                ['width' => 256, 'height' => 227, 'color' => '#ffffff'],
            ],
        ],
        'format' => [
            null,
            'jpg',
            '',
            80,
        ],
    ],
    'newsDetailsImage' => [
        'filters' => [
            [
                'reduce',
                ['width' => 400],
            ],
        ],
        'format' => [
            null,
            'jpg',
        ],
    ],
    'newsBigImage' => [
        'filters' => [
            [
                'fit',
                ['width' => 211, 'height' => 149],
            ],
            [
                'crop',
                ['width' => 211, 'height' => 149],
            ],
        ],
        'format' => [
            null,
            'png',
        ],
    ],
    'newsSmallImage' => [
        'filters' => [
            [
                'reduce',
                ['width' => 100, 'height' => 100],
            ],
        ],
        'format' => [
            null,
            'png',
        ],
    ],
    'newsCardImage' => [
        'filters' => [
            [
                'fit',
                ['width' => 610, 'height' => 671],
            ],
            [
                'crop',
                ['width' => 610, 'height' => 671],
            ],
        ],
        'format' => [
            null,
            'jpg',
            '',
            80,
        ],
    ],
    'personnelImage' => [
        'filters' => [
            [
                'reduce',
                ['width' => 310, 'height' => 310],
            ],
            [
                'crop',
                ['width' => 310, 'height' => 310, 'color' => '#ffffff'],
            ],
        ],
        'format' => [
            null,
            'jpg',
            '',
            80,
        ],
    ],
    'personnelImageDetailed' => [
        'filters' => [
            [
                'reduce',
                ['width' => 411, 'height' => 431],
            ],
            [
                'crop',
                ['width' => 411, 'height' => 431],
            ],
        ],
        'format' => [
            null,
            'png',
            '',
            80,
        ],
    ],
    'productionDetailsImage' => [
        'filters' => [
            [
                'reduce',
                ['width' => 400, 'height' => 400],
            ],
            [
                'crop',
                ['width' => 400, 'height' => 400, 'color' => '#ffffff'],
            ],
        ],
        'format' => [
            null,
            'jpg',
            '',
            80,
        ],
    ],
    'serviceShortImage' => [
        'filters' => [
            [
                'reduce',
                ['width' => 320, 'height' => 320],
            ],
        ],
        'format' => [
            null,
            'jpg',
        ],
    ],
    'serviceDetailsImage' => [
        'filters' => [
            [
                'reduce',
                ['width' => 400, 'height' => 400],
            ],
        ],
        'format' => [
            null,
            'jpg',
        ],
    ],
    'socialImage' => [
        'filters' => [
            [
                'reduce',
                ['width' => 500, 'height' => 500],
            ],
        ],
        'format' => [
            null,
            'jpg',
        ],
    ],
    'widgetImage' => [
        'filters' => [
            [
                'reduce',
                ['width' => 144, 'height' => 141],
            ],
            [
                'crop',
                ['width' => 144, 'height' => 141],
            ],
        ],
        'format' => [
            null,
            'png',
        ],
    ],
    'rssImage' => [
        'filters' => [
            [
                'reduce',
                ['width' => 400, 'height' => 300],
            ],
        ],
        'format' => [
            null,
            'jpg',
        ],
    ],
    'articleDefaultImage' => [
        'filters' => [
            [
                'reduce',
                ['width' => 1200],
            ],
        ],
        'format' => [
            null,
            'jpg',
        ],
    ],
    'newsItemIcon' => [
        'filters' => [
            [
                'reduce',
                ['width' => 64, 'height' => 64],
            ],
            [
                'crop',
                ['width' => 64, 'height' => 64],
            ],
        ],
        'format' => [
            null,
            null,
        ],
    ],
    'subArticleShortImage' => [
        'filters' => [
            [
                'reduce',
                ['width' => 1200],
            ],
        ],
        'format' => [
            null,
            'jpg',
        ],
    ],
];
