<?php

/**
 * Created by PhpStorm.
 * User: reneollino
 * Date: 26/09/14
 * Time: 11:15
 */
class UserExtractionProcedure extends ExtractionProcedure
{
    public $xmlObj = null;
    private $onlyUserNames = null;

    /**
     * Method to set various arguments to the procedure (like limitation, filtering ...)
     * @param $arguments
     * @return void
     */
    public function setProcedureArguments($arguments = null)
    {
        if ($arguments) {
            foreach ($arguments as $function_name => $args) {
                if (method_exists($this, $function_name)) {
                    $this->$function_name($args);
                }
            }
        }
    }

    /**
     * @param $userNames
     */
    private function onlyUserNames($userNames)
    {
        $this->onlyUserNames = $userNames;
    }

    /**
     * Method constructs the xml
     * @return SimpleXMLElement object
     */
    public function run()
    {
        if (!$this->xmlObj) {
            $this->xmlObj = new SimpleXMLElement('<?xml version="1.0"?><procedures></procedures>');
        }

        $structureManager = $this->getService('structureManager');

        $usersElement = $structureManager->getElementByMarker('users');
        $users = $structureManager->getElementsChildren($usersElement->id);

        foreach ($users as $user) {
            $linksManager = $this->getService(linksManager::class);
            $connectedIdList = $linksManager->getConnectedIdList($user->id, 'userRelation');
            $userName = $user->userName;

            if (!$connectedIdList || ($this->onlyUserNames && !in_array($userName, $this->onlyUserNames))) {
                continue;
            }

            $xmlChildObj = $this->xmlObj->addChild('AddUser');
            $xmlChildObj->addChild('username', $userName);
            $xmlChildObj->addChild('password', $user->password);
            $groupsXmlObj = $xmlChildObj->addChild('groups');

            foreach ($connectedIdList as $connectedId) {
                $usersGroupElement = $structureManager->getElementById($connectedId);
                if ($usersGroupElement->marker !== '') {
                    $groupsXmlObj->addChild('group', $usersGroupElement->marker);
                }
            }
        }

        return $this->xmlObj;
    }
}