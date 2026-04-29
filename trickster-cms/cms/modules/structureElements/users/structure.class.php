<?php

class usersElement extends structureElement
{
    use AutoMarkerTrait;
    public $languagesParentElementMarker = 'adminLanguages';
    public $dataResourceName = 'module_generic';
    protected $allowedTypes = ['user'];
    public $defaultActionName = 'showFullList';
    public $role = 'container';

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
    }

    protected function setMultiLanguageFields(&$multiLanguageFields)
    {
        $multiLanguageFields[] = 'title';
    }

    public function getUsers($data = [])
    {
        $db = $this->getService('db');
        $query = $db->table('module_user');
        $query->select('id');

        if (isset($data['sort'])) {
            if (isset($data['order'])) {
                $query->orderBy($data['sort'], $data['order']);
            } else {
                $query->orderBy($data['sort'], 'DESC');
            }
        }

        if (isset($data['filter_subscribe'])) {
            $query->where('subscribe', '=', $data['filter_subscribe']);
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
            $elements[] = $structureManager->getElementById($row['id']);
        }

        return $elements;
    }
}
