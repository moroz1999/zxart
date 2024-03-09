<?php

trait PublisherGroupProviderTrait
{
    protected $groupsList;
    protected $groupsIds;
    protected $publishersList;
    protected $publishersIds;

    public function getGroupsList()
    {
        return $this->getConnectedList('groups');
    }

    public function getGroupsIds()
    {
        return $this->getConnectedIds('groups');
    }

    public function getPublishersList()
    {
        return $this->getConnectedList('publishers');
    }

    public function getPublishersIds()
    {
        return $this->getConnectedIds('publishers');
    }

    protected function getConnectedList($type)
    {
        $property = $type . 'List';
        if ($this->$property === null) {
            $structureManager = $this->getService('structureManager');
            $this->$property = [];
            if ($connectedIds = $this->getConnectedIds($type)) {
                foreach ($connectedIds as $id) {
                    if ($group = $structureManager->getElementById($id)) {
                        $this->{$property}[] = $group;
                    }
                }
            }
        }
        return $this->$property;
    }

    protected function getConnectedIds($type = null)
    {
        $property = $type . 'Ids';

        if ($this->$property === null) {
            $linkType = $this->structureType . ucfirst($type);
            $this->$property = $this->getService('linksManager')->getConnectedIdList(
                $this->id,
                $linkType,
                'child'
            );
        }
        return $this->$property;
    }

}