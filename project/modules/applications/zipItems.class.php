<?php

/**
 * Save all works from author or party as zip archive
 *
 * Class zipItemsApplication
 */
class zipItemsApplication extends controllerApplication
{
    use CrawlerFilterTrait;

    protected $applicationName = 'zipItems';
    protected $mode = 'public';

    public $rendererName = 'json';
    /**
     * @var ConfigManager
     */
    protected $configManager;

    public function initialize()
    {
        set_time_limit(3 * 60);
        $this->configManager = $this->getService('ConfigManager');

        $this->startSession($this->mode);
        $this->createRenderer();
        return !$this->isCrawlerDetected();
    }

    /**
     * @return void
     */
    public function execute($controller)
    {
        $this->renderer->endOutputBuffering();

        $structureManager = $this->getService(
            'structureManager',
            [
                'rootUrl' => $controller->rootURL,
                'rootMarker' => $this->configManager->get('main.rootMarkerPublic'),
            ],
            true
        );
        $languagesManager = $this->getService('LanguagesManager');
        $structureManager->setRequestedPath([$languagesManager->getCurrentLanguageCode()]);

        $requestParameters = $controller->getParameters();

        if ($controller->getParameter('language')) {
            $languagesManager = $this->getService('LanguagesManager');
            $languagesManager->setCurrentLanguageCode($controller->getParameter('language'));
        }
        $queryParameters = [];
        $exportTypes = ['zxPicture', 'zxMusic'];

        if (isset($requestParameters['export'])) {
            $exportTypes = [trim($requestParameters['export'])];
        }
        $requestedAuthors = [];
        if (isset($requestParameters['filter'])) {
            $filtersStrings = explode(';', $requestParameters['filter']);
            foreach ($filtersStrings as $filterString) {
                if (trim($filterString) != '') {
                    $subStrings = explode('=', $filterString);
                    if (isset($subStrings[0])) {
                        $filterName = $subStrings[0];
                        if (isset($subStrings[1])) {
                            $queryParameters[$filterName] = explode(',', $subStrings[1]);
                        } else {
                            $queryParameters[$filterName] = true;
                        }

                        if ($filterName === 'authorId') {
                            $requestedAuthors = (array)$queryParameters[$filterName];
                        }
                    }
                }
            }
        }

        $structure = 'list';
        if (in_array($requestParameters['structure'], ['authors', 'parties', 'list'])) {
            $structure = $requestParameters['structure'];
        }

        $zipArchive = new ZipArchive();
        $zipName = 'zxart_files_' . time() . '.zip';
        $cachePath = $this->getService('PathsManager')->getPath('uploadsCache');
        $zipPath = $cachePath . $zipName;
        if ($zipArchive->open($zipPath, ZipArchive::CREATE)) {
            foreach ($exportTypes as $exportType) {
                $resultTypes = [$exportType];

                if (count($resultTypes) && count($queryParameters)) {
                    /**
                     * @var ApiQueriesManager $apiQueriesManager
                     */
                    $apiQueriesManager = $this->getService('ApiQueriesManager');
                    if ($apiQuery = $apiQueriesManager->getQuery()) {
                        $apiQuery->setFiltrationParameters($queryParameters);
                        $apiQuery->setExportType($exportType); // objects to output
                        $apiQuery->setResultTypes($resultTypes); // object types to query                        

                        if ($result = $apiQuery->getQueryResult()) {
                            if (!is_dir($cachePath)) {
                                mkdir(
                                    $cachePath,
                                    $this->getService('ConfigManager')
                                        ->get('paths.defaultCachePermissions'),
                                    true
                                );
                            }

                            foreach ($result[$exportType] as $itemElement) {
                                $paths = [];
                                $fileName = '';
                                if ($structure === 'authors') {
                                    foreach ($itemElement->getRealAuthorsList() as $author) {
                                        if (!$requestedAuthors || in_array($author->id, $requestedAuthors)) {
                                            $location = TranslitHelper::convert(
                                                preg_replace(
                                                    '#[\?\/\<\>\\\:\*\|\"\.]*#ui',
                                                    '',
                                                    html_entity_decode($author->title, ENT_QUOTES)
                                                )
                                            );
                                            $path = $location . '/';
                                            if ($content = $this->generateAuthorText($author)) {
                                                $zipArchive->addFromString($path . $location . '.txt', $content);
                                            }
                                            if ($itemElement->year) {
                                                $paths[] = $path . $itemElement->year . '/';
                                            } else {
                                                $paths[] = $path;
                                            }
                                        }
                                    }
                                    $fileName = $itemElement->getFileName('original', false, false, false, false);
                                } elseif ($structure === 'parties') {
                                    if ($party = $itemElement->getPartyElement()) {
                                        $location = TranslitHelper::convert(
                                            preg_replace(
                                                '#[\?\/\<\>\\\:\*\|\"]*#ui',
                                                '',
                                                html_entity_decode($party->title, ENT_QUOTES)
                                            )
                                        );
                                        $path = $location . '/';
                                        if ($itemElement->compo) {
                                            $paths[] = $path . $itemElement->compo . '/';
                                        } else {
                                            $paths[] = $path;
                                        }
                                    }
                                    $fileName = $itemElement->getFileName(
                                        'original',
                                        false,
                                        false,
                                        true,
                                        false,
                                        false,
                                        true
                                    );
                                } elseif ($structure === 'list') {
                                    $path = '/';
                                    $paths[] = $path;
                                    $fileName = $itemElement->getFileName(
                                        'original',
                                        false,
                                        false,
                                        true,
                                        true,
                                        true,
                                        false,
                                        true
                                    );
                                }
                                $fileName = $this->sanitizeName($fileName);
                                $originalPath = $itemElement->getOriginalPath();
                                if (is_file($originalPath)) {
                                    foreach ($paths as $path) {
                                        $zipArchive->addFile($originalPath, $path . $fileName);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $zipArchive->close();
        header('Content-Disposition: attachment; filename="' . $zipName . '"');
        header('Content-type: application/zip');
        readfile($zipPath);
        unlink($zipPath);
    }

    /**
     * @return null|string|string[]
     *
     * @psalm-return array<string>|null|string
     */
    protected function sanitizeName($fileName): array|string|null
    {
        $fileName = html_entity_decode($fileName, ENT_QUOTES);
        $fileName = TranslitHelper::convert($fileName);
        $fileName = preg_replace('/[^a-z0-9.]+/i', '-', $fileName);
        return $fileName;
    }

    public function generateAuthorText($author): string
    {
        $text = '';
        if ($author->realName != $author->title) {
            $text .= 'Nickname: ' . $author->title . "\n\r";
        }
        if ($author->realName) {
            $text .= 'Name: ' . $author->realName . "\n\r";
        }
        if ($author->group) {
            $text .= 'Group: ' . $author->group . "\n\r";
        }
        $text .= "\n\r";
        if ($author->getCityTitle()) {
            $text .= 'City: ' . $author->getCityTitle() . "\n\r";
        }
        if ($author->getCountryTitle()) {
            $text .= 'Country: ' . $author->getCountryTitle() . "\n\r";
        }
        $text .= 'Profile URL: ' . $author->getUrl() . "\n\r";
        return $text;
    }

    public function getUrlName()
    {
        return '';
    }
}