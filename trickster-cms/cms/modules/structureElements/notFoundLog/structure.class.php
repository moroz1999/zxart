<?php

class notFoundLogElement extends structureElement
{
    public $languagesParentElementMarker = 'adminLanguages';
    public $dataResourceName = 'module_generic';
    public $defaultActionName = 'show';
    public $role = 'container';
    public $pager;
    protected $redirectsElement;

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['errorUrl'] = 'text';
        $moduleStructure['redirectUrl'] = 'text';
        $moduleStructure['ignoreRedirected'] = 'text';
    }

    protected function setMultiLanguageFields(&$multiLanguageFields)
    {
        $multiLanguageFields[] = 'title';
    }

    protected function getTabsList()
    {
        return [
            'show',
            'showForm',
        ];
    }

    public function getLogData($filters)
    {
        $tableName = '404_log';
        $db = $this->getService('db');
        $query = $db->table($tableName);

        $conditions = [];
        if ($filters["errorUrl"]) {
            $conditions[] = [
                'column' => 'errorUrl',
                'action' => 'like',
                'argument' => "%%" . $filters["errorUrl"] . "%%",
            ];
        }
        if ($filters["ignoreRedirected"] != 'include') {
            $conditions[] = [
                'column' => 'redirectionId',
                'action' => '=',
                'argument' => "0",
            ];
        }
        $conditions[] = [
            'column' => 'hidden',
            'action' => '=',
            'argument' => "0",
        ];
        foreach ($conditions as $condition) {
            $query->where($condition['column'], $condition['action'], $condition['argument']);
        }

        $elementsCount = $query->count();

        // set pager
        $page = 0;
        $elementsOnPage = 30;
        $controller = controller::getInstance();
        if ($controller->getParameter('page')) {
            $page = (int)$controller->getParameter('page');
        }
        $pager = new pager($this->URL, $elementsCount, $elementsOnPage, $page, 'page');
        $this->pager = $pager;

        // prepare query data
        $columns = [
            'id',
            'errorUrl',
            'httpReferer',
            'count',
            'date',
            'redirectionId',
        ];
        // submit query
        $query = $db->table($tableName);
        foreach ($columns as $column) {
            $query->addSelect($column);
        }
        if (isset($columns_raw)) {
            $query->addSelect($query->raw($columns_raw));
        }
        foreach ($conditions as $condition) {
            $query->where($condition['column'], $condition['action'], $condition['argument']);
        }
        $query = $query->orderBy('count', 'desc');
        $result = $query->skip($pager->startElement)->take($elementsOnPage)->get();
        return $this->formatLogData($result);
    }

    protected function formatLogData(&$data)
    {
        if (is_array($data) && count($data)) {
            if ($redirectsElement = $this->getRedirectsElement()) {
                foreach ($data as &$logLine) {
                    $d = $logLine['date'];
                    $logLine['date'] = date("d.m.Y H:i", $d);
                    if (isset($logLine["count(errorUrl)"])) {
                        $logLine["requestCount"] = $logLine["count(errorUrl)"];
                    } else {
                        $logLine["requestCount"] = "";
                    }
                    if ($logLine['redirectionId']) {
                        if ($redirectionElement = $this->getService('structureManager')
                            ->getElementById($logLine['redirectionId'])
                        ) {
                            $logLine["newRedirectUrl"] = $redirectionElement->URL;
                        }
                    } else {
                        $logLine["newRedirectUrl"] = $redirectsElement->URL . 'type:redirect/action:showForm/' . "logId:" . $logLine["id"] . "/";
                    }
                }
            }
            return $data;
        }
        return false;
    }

    public function getRedirectsElement()
    {
        if (is_null($this->redirectsElement)) {
            $structureManager = $this->getService('structureManager');
            $this->redirectsElement = $structureManager->getElementByMarker("redirects");
        }
        return $this->redirectsElement;
    }

    public function getLogs($data = [])
    {
        $db = $this->getService('db');
        $query = $db->table('404_log');

        $query->select()
            ->selectRaw('FROM_UNIXTIME(date, \'%d.%m.%Y %H:%i\') AS dateFormatted');

        if (isset($data['sort'])) {
            if (isset($data['order'])) {
                $query->orderBy($data['sort'], $data['order']);
            } else {
                $query->orderBy($data['sort'], 'DESC');
            }
        }

        if (isset($data['limit'])) {
            if (isset($data['page'])) {
                $page = $data['page'];
            } else {
                $page = 1;
            }

            $query->forPage($page, $data['limit']);
        }
        return $query->get();
    }
}