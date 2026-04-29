<?php
/**
 * Created by PhpStorm.
 * User: reneollino
 * Date: 26/09/14
 * Time: 11:15
 */

class UserGroupExtractionProcedure extends ExtractionProcedure
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

        $userGroupsObj = $structureManager->getElementByMarker('userGroups');
        $userGroups = $structureManager->getElementsChildren($userGroupsObj->id);

        foreach ($userGroups as $userGroup) {
            $name = $userGroup->structureName;
            $description = $userGroup->description;
            $marker = $userGroup->marker;

            $xmlChildObj = $this->xmlObj->addChild('AddUserGroup');
            $xmlChildObj->addChild('name', $name);
            $xmlChildObj->addChild('description', $description);
            $xmlChildObj->addChild('marker', $marker);
        }

        return $this->xmlObj;
    }
}