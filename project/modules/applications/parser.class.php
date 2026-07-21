<?php

use ZxArt\FileParsing\ZxParsingItem;
use ZxArt\FileParsing\ZxParsingManager;
use ZxArt\Urls\EntityUrlResolver;

// parser app for search by file

class parserApplication extends controllerApplication
{
    public $rendererName = 'json';
    protected $applicationName = 'parser';
    private $filePath;
    private $fileName;
    /**
     * @var \Illuminate\Database\Connection
     */
    private $db;
    /**
     * @var structureManager
     */
    private $structureManager;

    /**
     * @return void
     */
    public function initialize()
    {
        ignore_user_abort(1);
        set_time_limit(3 * 60);
        $this->createRenderer();
    }

    private function acceptFile(): bool
    {
        if (!empty($_FILES) && !empty($_FILES['file'])) {
            $file = $_FILES['file'];
            if (is_file($file['tmp_name']) && $file['size'] <= 1024 * 1024 * 50) {
                $this->fileName = $file['name'];
                $this->filePath = $cachePath = $this->pathsManager->getPath('uploadsCache') . uniqid($this->fileName);
                if (move_uploaded_file($file['tmp_name'], $this->filePath)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @return void
     */
    public function execute($controller)
    {
        if ($this->acceptFile()) {
            $this->structureManager = $this->getService(
                'structureManager',
                [
                    'rootUrl' => $controller->rootURL,
                    'rootMarker' => $this->getService(ConfigManager::class)->get('main.rootMarkerPublic'),
                ],
            );
            $this->db = $this->getService('db');
            $zxParsingManager = $this->getService(ZxParsingManager::class);
            if ($structure = $zxParsingManager->parseFileStructure($this->filePath, $this->fileName)) {
                $response = $this->prepareResponse($structure);
                $this->renderer->assign('responseStatus', 'success');
                $this->renderer->assign('responseData', $response);
            }
            if (is_file($this->filePath)) {
                unlink($this->filePath);
            }
        }
        $this->renderer->display();
    }

    /**
     * @param ZxParsingItem[] $structure
     *
     * @psalm-param array<ZxParsingItem> $structure
     *
     * @return array[]
     *
     * @psalm-return list{0?: array,...}
     */
    private function prepareResponse(array $structure): array
    {
        $response = [];
        foreach ($structure as $item) {
            $response[] = $this->exportItem($item);
        }
        return $response;
    }

    /**
     * @param ZxParsingItem $item
     * @return array
     */
    private function exportItem($item)
    {
        $releases = $this->loadReleasesData($item->getMd5());
        $subItems = $item->getItems();

        $export = [
            'name' => $item->getItemName(),
            'type' => $item->getType(),
            'md5' => $item->getMd5(),
            'files' => [],
            'notFound' => !$releases && !$subItems,
            'releases' => $releases,
        ];

        if ($subItems) {
            foreach ($subItems as $subItem) {
                if ($subItem) {
                    $item = $this->exportItem($subItem);
                    $export['files'][] = $item;
                    $export['notFound'] = $export['notFound'] | $item['notFound'];
                }
            }
        }


        return $export;
    }

    /**
     * @return list<array{title: string, id: int, url: string, year: mixed, authors: list<array{url: string, title: string, id: mixed, type: mixed}>}>
     */
    private function loadReleasesData($md5): array
    {
        $releases = [];
        $result = $this->db->table('files_registry')
            ->where('md5', $md5)
            ->groupBy('elementId')
            ->get();
        foreach ($result as $item) {
            if ($element = $this->structureManager->getElementById($item['elementId'])) {
                if ($element->structureType === 'zxRelease') {
                    /**
                     * @var zxReleaseElement $element
                     */
                    $authors = $element->getReleaseBy();
                } else {
                    $authors = $element->getAuthorsList();
                }
                $releases[] = [
                    'title' => html_entity_decode($element->getTitle(), ENT_QUOTES),
                    'id' => $element->getPersistedId(),
                    'url' => $this->urlFor($element),
                    'year' => $element->getYear(),
                    'authors' => $this->exportAuthors($authors),
                ];
            }
        }

        return $releases;
    }

    /**
     * @return list<array{url: string, title: string, id: mixed, type: mixed}>
     */
    private function exportAuthors(mixed $authors): array
    {
        $releaseBy = [];
        foreach (is_array($authors) ? $authors : [] as $author) {
            if (!$author instanceof structureElement) {
                continue;
            }
            $releaseBy[] = [
                'url' => $this->urlFor($author),
                'title' => html_entity_decode((string)$author->getTitle(), ENT_QUOTES),
                'id' => $author->getId(),
                'type' => $author->structureType,
            ];
        }
        return $releaseBy;
    }

    /** Clean SPA URL for a matched element, so the results link inside the Angular app. */
    private function urlFor(structureElement $element): string
    {
        return $this->getService(EntityUrlResolver::class)->urlFor($element);
    }

    public function getUrlName()
    {
        return '';
    }
}


