<?php
declare(strict_types=1);


namespace App\Structure;

use App\Paths\PathsManager;
use DependencyInjectionContextInterface;
use DependencyInjectionContextTrait;
use errorLogger;
use structureElementAction;

class ActionFactory extends errorLogger implements DependencyInjectionContextInterface
{
    use DependencyInjectionContextTrait;

    public function makeActionObject($elementType, $actionName): ?structureElementAction
    {
        $actionObjectName = $actionName . ucfirst($elementType);

        if (!$actionObjectName || !class_exists($actionObjectName, false)) {
            $fileName = "action." . $actionName . '.class.php';
            $pathsManager = $this->getService(PathsManager::class);
            $elementsPath = $pathsManager->getRelativePath('structureElements');
            $sharedPath = $pathsManager->getRelativePath('sharedActions');
            if ($elementFilePath = $pathsManager->getIncludeFilePath($elementsPath . $elementType . '/' . $fileName)
            ) {
                include_once($elementFilePath);
            } elseif ($sharedFilePath = $pathsManager->getIncludeFilePath($sharedPath . $fileName)) {
                include_once($sharedFilePath);
                $actionObjectName = $actionName . 'Shared';
            } else {
                $actionObjectName = null;
                $this->logError('Structure element action class file ' . $elementType . '/' . $actionName . ' doesnt exist');
            }
        }

        if ($actionObjectName !== null && class_exists($actionObjectName, false)) {
            /**
             * @var structureElementAction $actionObject
             */
            $actionObject = new $actionObjectName();
            $actionObject->actionName = $actionName;
            return $actionObject;
        }
        return null;
    }
}
