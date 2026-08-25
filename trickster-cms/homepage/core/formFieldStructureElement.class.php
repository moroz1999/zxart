<?php

use App\Users\CurrentUserService;

abstract class formFieldStructureElement extends structureElement
{
    public function getAutoCompleteValue()
    {
        $value = null;
        $currentUserService = $this->getService(CurrentUserService::class);
        $user = $currentUserService->getCurrentUser();
        if ($this->autocomplete == 'service') {
            $structureManager = $this->getService('structureManager');
            $controller = $this->getService(controller::class);
            if ($controller->getParameter('service')) {
                $serviceId = $controller->getParameter('service');
                if ($service = $structureManager->getElementById($serviceId)) {
                    $value = $service->title;
                }
            }
        } elseif ($this->autocomplete == 'userName') {
            $value = $user->userName;
        } elseif ($this->autocomplete == 'email') {
            $value = $user->email;
        }

        return $value;
    }

    public function getDataChunkType()
    {
        return $this->dataChunk;
    }
}




