<?php

use App\Users\CurrentUserService;

class actionsLogElement extends structureElement
{
    public $languagesParentElementMarker = 'adminLanguages';
    public $dataResourceName = 'module_generic';
    public $defaultActionName = 'show';
    public $role = 'container';
    protected $pager;

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['elementType'] = 'text';
        $moduleStructure['elementId'] = 'text';
        $moduleStructure['elementName'] = 'text';
        $moduleStructure['userId'] = 'text';
        $moduleStructure['userIP'] = 'text';
        $moduleStructure['periodStart'] = 'text';
        $moduleStructure['periodEnd'] = 'text';
        $moduleStructure['action'] = 'text';
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

    protected function getFilters($formData, &$filterNames)
    {
        $filterData = [];
        $currentUserService = $this->getService(CurrentUserService::class);
        $user = $currentUserService->getCurrentUser();

        foreach ($filterNames as &$filterName) {
            if (isset($formData[$filterName])) {
                $formData[$filterName] = trim($formData[$filterName]);
                $this->$filterName = $formData[$filterName];
                $user->setStorageAttribute("actionsLog_$filterName", $formData[$filterName]);
                $filterData[$filterName] = $formData[$filterName];
            } else {
                if ($filterData[$filterName] = $user->getStorageAttribute("actionsLog_$filterName")) {
                    $this->$filterName = $filterData[$filterName];
                }
            }
        }
        return $filterData;
    }

    public function getActionLogData()
    {
        $filterNames = [
            'periodStart',
            'periodEnd',
            'elementType',
            'elementId',
            'elementName',
            'userId',
            'userIP',
            'action',
        ];

        $filters = $this->getFilters($this->getFormData(), $filterNames);

        $tableName = 'actions_log';
        $db = $this->getService('db');
        $query = $db->table($tableName);

        $conditions = [];
        if ($filters['elementId']) {
            $conditions[] = [
                'column' => 'elementId',
                'action' => '=',
                'argument' => $filters['elementId'],
            ];
        }
        if ($filters['action']) {
            $conditions[] = [
                'column' => 'action',
                'action' => '=',
                'argument' => $filters['action'],
            ];
        }
        if ($filters['elementType']) {
            $conditions[] = [
                'column' => 'elementType',
                'action' => '=',
                'argument' => $filters['elementType'],
            ];
        }
        if ($filters['elementName']) {
            $conditions[] = [
                'column' => 'elementName',
                'action' => 'like',
                'argument' => '%' . $filters['elementName'] . '%',
            ];
        }
        if ($filters['userId']) {
            $conditions[] = [
                'column' => 'userId',
                'action' => '=',
                'argument' => $filters['userId'],
            ];
        }
        if ($filters['userIP']) {
            $conditions[] = [
                'column' => 'userIP',
                'action' => '=',
                'argument' => $filters['userIP'],
            ];
        }
        if ($filters['periodStart'] && $filters['periodEnd']) {
            $conditions[] = [
                'column' => 'date',
                'action' => '>=',
                'argument' => strtotime($filters['periodStart']),
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
        $elementsOnPage = 50;
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
            'elementId',
            'elementType',
            'action',
            'userId',
            'userName',
            'elementName',
            'userIP',
            'date',
        ];
        $orderBy = ['date', 'desc'];

        // submit query
        $query = $db->table($tableName)->select($columns);
        foreach ($conditions as $condition) {
            $query->where($condition['column'], $condition['action'], $condition['argument']);
        }
        $result = $query->orderBy($orderBy[0], $orderBy[1])->skip($pager->startElement)->take($elementsOnPage)->get();
        return $this->formatActionLogData($result);
    }

    protected function formatActionLogData(&$data)
    {
        if (is_array($data) && count($data)) {
            foreach ($data as &$logLine) {
                $d = $logLine['date'];
                $logLine['date'] = date("d.m.Y H:i", $d);
            }
            return $data;
        }
        return false;
    }

    public function getLogs($data = [])
    {
        $db = $this->getService('db');
        $query = $db->table('actions_log')
            ->select();

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

        $elements = [];

        $structureManager = $this->getService('structureManager');

        foreach ($query->get() as $row) {
            $info = [
                'dateCreated' => '',
                'URL' => '',
                'elementName' => '',
                'elementType' => $row['elementType'],
                'action' => $row['action'],
                'userName' => $row['userName'],
                'date' => date("d.m.Y H:i", $row['date']),
            ];
            if ($element = $structureManager->getElementById($row['elementId'])) {
                $info['dateCreated'] = $element->dateCreated;
                $info['URL'] = $element->URL;
                $info['elementName'] = $element->getTitle();
            }
            $elements[] = $info;
        }

        return $elements;
    }

    public function getPager()
    {
        if ($this->pager === null) {
            $this->getActionLogData();
        }
        return $this->pager;
    }
}



