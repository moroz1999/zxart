<?php
// parser app for search by file
use ZxArt\FileParsing\ZxParsingItem;
use ZxArt\FileParsing\ZxParsingManager;

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
                $this->filePath = $cachePath = $this->getService('PathsManager')->getPath('uploadsCache') . uniqid($this->fileName);
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
                    'rootMarker' => $this->getService('ConfigManager')->get('main.rootMarkerPublic'),
                ],
            );
            $this->db = $this->getService('db');
            /**
             * @var ZxParsingManager $zxParsingManager
             */
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
     * @return ((mixed|string)[][]|int|mixed|string)[][]
     *
     * @psalm-return list{0?: array{title: string, id: int, url: mixed, year: mixed, authors: list{0?: array{url: mixed, title: string, id: mixed, type: mixed},...}},...}
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
                    $releaseBy = [];
                    foreach ($authors as $author) {
                        $releaseBy[] = [
                            'url' => $author->getUrl(),
                            'title' => html_entity_decode($author->getTitle(), ENT_QUOTES),
                            'id' => $author->getId(),
                            'type' => $author->structureType,
                        ];
                    }
                } else {
                    /**
                     * @var ZxArtItem
                     */
                    $authors = $element->getAuthorsList();
                    $releaseBy = [];
                    foreach ($authors as $author) {
                        $releaseBy[] = [
                            'url' => $author->getUrl(),
                            'title' => html_entity_decode($author->getTitle(), ENT_QUOTES),
                            'id' => $author->getId(),
                            'type' => $author->structureType,
                        ];
                    }
                }
                $releases[] = [
                    'title' => html_entity_decode($element->getTitle(), ENT_QUOTES),
                    'id' => $element->getPersistedId(),
                    'url' => $element->getUrl(),
                    'year' => $element->getYear(),
                    'authors' => $releaseBy,
                ];
            }
        }

        return $releases;
    }

    public function getUrlName()
    {
        return '';
    }
}

