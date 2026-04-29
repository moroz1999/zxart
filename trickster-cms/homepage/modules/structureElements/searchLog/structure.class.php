<?php

class searchLogElement extends structureElement
{
    public $languagesParentElementMarker = 'adminLanguages';
    public $dataResourceName = 'module_generic';
    public $defaultActionName = 'show';
    public $role = 'container';
    public $pager;

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['phrase'] = 'text';
        $moduleStructure['bZeroResultsOnly'] = 'checkbox';
        $moduleStructure['bNotClicked'] = 'checkbox';
        $moduleStructure['periodStart'] = 'text';
        $moduleStructure['periodEnd'] = 'text';
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
            'showPositions',
            'showPrivileges',
        ];
    }

    public function getLogData(&$filters)
    {
        $tableName = 'search_log';
        $db = $this->getService('db');
        $query = $db->table($tableName);

        $conditions = [];
        if ($filters["phrase"]) {
            $conditions[] = [
                'column' => 'phrase',
                'action' => 'like',
                'argument' => '%' . $filters["phrase"] . '%',
            ];
        }
        if ($filters["bNotClicked"]) {
            $conditions[] = [
                'column' => 'bClicked',
                'action' => '=',
                'argument' => 0,
            ];
        }
        if ($filters["bZeroResultsOnly"]) {
            $conditions[] = [
                'column' => 'resultsCount',
                'action' => '=',
                'argument' => 0,
            ];
        }
        if ($filters["periodStart"] && $filters["periodEnd"]) {
            $conditions[] = [
                'column' => 'date',
                'action' => '>=',
                'argument' => strtotime($filters["periodStart"]),
            ];
            $conditions[] = [
                'column' => 'date',
                'action' => '<=',
                'argument' => strtotime($filters["periodEnd"]),
            ];
        }

        foreach ($conditions as $condition) {
            $query->where($condition['column'], $condition['action'], $condition['argument']);
        }

        // set pager
        $pagerURL = $this->URL;
        $elementsOnPage = 30;
        $elementsCount = $query->count();
        $page = 0;
        $controller = controller::getInstance();
        if ($controller->getParameter('page')) {
            $page = intval($controller->getParameter('page'));
        }
        $pager = new pager($pagerURL, $elementsCount, $elementsOnPage, $page, 'page');
        $this->pager = $pager;

        // prepare query data
        $columns = [
            'id',
            'phrase',
            'resultsCount',
            'bClicked',
            'date',
            'visitorId',
        ];
        $orderBy = ['id', 'desc'];

        // submit query
        $query = $db->table($tableName)->select($columns);
        foreach ($conditions as $condition) {
            $query->where($condition['column'], $condition['action'], $condition['argument']);
        }
        $logData = $query->orderBy($orderBy[0], $orderBy[1])->skip($pager->startElement)->take($elementsOnPage)->get();
        return $this->formatActionLogData($logData);
    }

    protected function formatActionLogData(&$data)
    {
        $structureManager = $this->getService('structureManager');
        $visitorElement = $structureManager->getElementByMarker('Visitors');
        $url = $visitorElement->URL;
        $url = $url.'visitor:';
        if (is_array($data) && count($data)) {
            foreach ($data as &$logLine) {
                $d = $logLine['date'];
                $logLine['date'] = date("d.m.Y H:i", $d);
                if($logLine['visitorId'] != 0 || !empty($logLine['visitorId'])) {
                    $logLine['visitorURL'] = $url.$logLine['visitorId'].'/';
                }
            }
            return $data;
        }
        return false;
    }
}


