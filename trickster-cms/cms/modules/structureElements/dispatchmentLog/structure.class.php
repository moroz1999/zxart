<?php

class dispatchmentLogElement extends structureElement
{
    public $languagesParentElementMarker = 'adminLanguages';
    public $dataResourceName = 'module_generic';
    public $defaultActionName = 'show';
    public $role = 'container';
    public $pager;
    public $actionsLogData;

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['email'] = 'text';
        $moduleStructure['dispatchmentId'] = 'text';
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
        ];
    }

    public function getLogData(&$filters)
    {
        $db = $this->getService('db');
        $query = $db->table('email_dispatchments_history as history');
        if ($filters['dispatchmentId']) {
            $query->where('history.dispatchmentId', '=', $filters['dispatchmentId']);
        }
        if ($filters['email']) {
            $conditions[] = [
                'column' => 'email',
                'action' => '=',
                'argument' => $filters['email'],
            ];
        }
        if ($filters['periodStart'] && $filters['periodEnd']) {
            $query->where('history.startTime', '>=', $filters['periodStart']);
            $query->where('history.startTime', '<=', $filters['periodEnd']);
        }

        // set pager
        $page = 0;
        $controller = controller::getInstance();
        if ($controller->getParameter('page')) {
            $page = intval($controller->getParameter('page'));
        }
        $elementsOnPage = 50;
        $pager = new pager($this->URL, $query->count(), $elementsOnPage, $page, 'page');
        $this->pager = $pager;

        $columns = [
            'history.id',
            'history.dispatchmentId',
            'history.name',
            'history.email',
            'history.startTime',
            'history.status',
            'dispatch.fromEmail',
            'dispatch.fromName',
            'dispatch.subject',
            'dispatch.data',
        ];
        $orderBy = ['startTime', 'desc'];
        $query->leftJoin('email_dispatchments as dispatch', 'history.dispatchmentId', '=', 'dispatch.id');

        $query->select($columns);
        if ($rows = $query->orderBy($orderBy[0], $orderBy[1])
            ->skip($pager->startElement)
            ->take($elementsOnPage)
            ->get()) {
            $secret = $this->getService(ConfigManager::class)->get('emails.dispatchmentSecret');

            foreach ($rows as &$row) {
                $row['startTime'] = date('d.m.Y H:i', $row['startTime']);
                $row['link'] = $controller->baseURL . 'emails/action:view/id:' . $row['id'] . '/email:' . $row['email'] . '/key:' . hash_hmac('sha256', $row['email'], $secret);
            }
        }
        return $rows;
    }
}