<?php

use App\Users\CurrentUserService;
use ZxArt\Releases\Services\ReleaseCloner;

class cloneZxRelease extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param zxReleaseElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $clonedElement = $this->getService(ReleaseCloner::class)->clone($structureElement);
        $this->getService(CurrentUserService::class)->getCurrentUser()->refreshPrivileges();

        $this->respondFormSaved($controller, $clonedElement);
    }
}
