<?php

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

    public function initialize()
    {
        ignore_user_abort(1);
        set_time_limit(60 * 60);
        $this->createRenderer();
    }

    private function acceptFile()
    {
        if (!empty($_FILES) && !empty($_FILES['file'])) {
            $file = $_FILES['file'];
            if (is_file($file['tmp_name']) && $file['size'] <= 1024 * 1024 * 100) {
                $this->fileName = $file['name'];
                $this->filePath = $cachePath = $this->getService('PathsManager')->getPath('uploadsCache') . uniqid($this->fileName);
                if (move_uploaded_file($file['tmp_name'], $this->filePath)) {
                    return true;
                }
            }
        }
        return false;
    }

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
            $zxParsingManager = $this->getService('ZxParsingManager');
            if ($structure = $zxParsingManager->getFileStructure($this->filePath, $this->fileName)) {
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

    private function prepareResponse($structure)
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
        $export = [
            'name' => $item->getItemName(),
            'type' => $item->getType(),
            'md5' => $item->getMd5(),
            'files' => [],
        ];
        $export['releases'] = $this->loadReleasesData($item->getMd5());
        if ($subItems = $item->getItems()) {
            foreach ($subItems as $subItem) {
                if ($subItem) {
                    $export['files'][] = $this->exportItem($subItem);
                }
            }
        }

        return $export;
    }

    private function loadReleasesData($md5)
    {
        $releases = [];
        $result = $this->db->table('files_registry')
            ->where('md5', $md5)
            ->groupBy('elementId')
            ->get();
        foreach ($result as $item) {
            if ($element = $this->structureManager->getElementById($item['elementId'])) {
                if ($element->structureType === 'zxRelease'){
                    /**
                     * @var zxReleaseElement $element
                     */
                    $authors = $element->getReleaseBy();
                    $releaseBy = [];
                    foreach ($authors as $author) {
                        $releaseBy[] = [
                            'url' => $author->getUrl(),
                            'title' => $author->getTitle(),
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
                            'title' => $author->getTitle(),
                            'id' => $author->getId(),
                            'type' => $author->structureType,
                        ];
                    }
                }
                $releases[] = [
                    'title' => $element->getTitle(),
                    'id' => $element->getId(),
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

