<?php

/**
 * Created by PhpStorm.
 * User: reneollino
 * Date: 26/09/14
 * Time: 11:15
 */
class UserPrivilegeExtractionProcedure extends ExtractionProcedure
{
    public $xmlObj = null;

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
     * Method constructs the xml
     * @return SimpleXMLElement object
     */
    public function run()
    {
        if (!$this->xmlObj) {
            $this->xmlObj = new SimpleXMLElement('<?xml version="1.0"?><procedures></procedures>');
        }

        $structureManager = $this->getService('structureManager');

        $privilegesManager = $this->getService(privilegesManager::class);
        $userPrivileges = $privilegesManager->getPrivileges();

        $privilegesArray = [];
        foreach ($userPrivileges as $userPrivilege) {
            $userGroupMarker = '';
            $targetElementMarker = '';

            $userGroupElement = $structureManager->getElementById($userPrivilege->userId);
            if ($userGroupElement) {
                $userGroupMarker = $userGroupElement->marker;
            }
            $targetElement = $structureManager->getElementById($userPrivilege->elementId);
            if ($targetElement) {
                $targetElementMarker = $targetElement->marker;
            }
            $action = $userPrivilege->action;

            if ($targetElementMarker && $userGroupMarker && $action != 'void') {
                $moduleType = $userPrivilege->module;
                $privilege = $userPrivilege->type ? "allow" : "deny";

                $uniqueKey = $userGroupMarker . $targetElementMarker . $moduleType . $action . $privilege;
                $privilegesArray[$uniqueKey] = [
                    'userGroupMarker' => $userGroupMarker,
                    'targetElementMarker' => $targetElementMarker,
                    'moduleType' => $moduleType,
                    'action' => $action,
                    'privilege' => $privilege,
                ];
            }
        }

        foreach ($privilegesArray as $privilege) {
            $xmlChildObj = $this->xmlObj->addChild('AddUserPrivilege');
            $xmlChildObj->addChild('userGroupMarker', $privilege['userGroupMarker']);
            $xmlChildObj->addChild('targetElementMarker', $privilege['targetElementMarker']);
            $xmlChildObj->addChild('moduleType', $privilege['moduleType']);
            $xmlChildObj->addChild('action', $privilege['action']);
            $xmlChildObj->addChild('privilege', $privilege['privilege']);
        }

        return $this->xmlObj;
    }
}