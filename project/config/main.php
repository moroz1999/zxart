<?php return [
    'enabledPlugins' => [
        'cms' => 'cms/',
        'homepage' => 'homepage/',
        'project' => 'project/',
    ],
    'publicSessionLifeTime' => 3600,
    'adminSessionLifeTime' => 86400,
    'defaultSessionLifeTime' => 1440,
    'publicTheme' => 'project',
    'rssTheme' => 'projectRss',
    'googleAnalyticsDomain' => '',
    'googleAnalyticsId' => '',
    'pageAmountProducts' => 10,
    'availablePageAmountProducts' => [20, 30, 50],
    'timeZone' => 'Europe/Tallinn',
    'errorReporting' => E_ALL,
    'protocol' => 'http://',
    'defaultRootElementId' => '1',
    'rootMarkerPublic' => 'public_root',
    'rootMarkerAdmin' => 'admin_root',
    'bestGuesssTypes' => ['zxProd', 'zxRelease'],
];