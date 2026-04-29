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
        } elseif ($this->autocomplete == 'company') {
            $value = $user->company;
        } elseif ($this->autocomplete == 'userName') {
            $value = $user->userName;
        } elseif ($this->autocomplete == 'fullName') {
            $value = $user->firstName . ' ' . $user->lastName;
        } elseif ($this->autocomplete == 'firstName') {
            $value = $user->firstName;
        } elseif ($this->autocomplete == 'lastName') {
            $value = $user->lastName;
        } elseif ($this->autocomplete == 'email') {
            $value = $user->email;
        } elseif ($this->autocomplete == 'phone') {
            $value = $user->phone;
        } elseif ($this->autocomplete == 'address') {
            $value = $user->address;
        } elseif ($this->autocomplete == 'city') {
            $value = $user->city;
        } elseif ($this->autocomplete == 'country') {
            $value = $user->country;
        } elseif ($this->autocomplete == 'postIndex') {
            $value = $user->postIndex;
        }

        return $value;
    }

    public function getDataChunkType()
    {
        return $this->dataChunk;
    }
}




