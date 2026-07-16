<?php

declare(strict_types=1);

namespace ZxArt\Users;

use App\Users\CurrentUserService;
use structureManager;
use userElement;
use ZxArt\Users\Rest\UserProfileRestDto;

/**
 * Self-service profile read/save for the authenticated user. Operates directly
 * on the current user's element and only touches the safe contact/info fields
 * (no userGroups, privileges, verification/ban flags, or password — those stay
 * admin-only / dedicated flows).
 */
readonly class UserProfileService
{
    /** Fields a user may edit about themselves. */
    private const EDITABLE_FIELDS = [
        'company', 'firstName', 'lastName', 'address', 'city',
        'postIndex', 'country', 'email', 'phone', 'website',
    ];
    private const BOOL_FIELDS = ['subscribe', 'showemail'];

    public function __construct(
        private CurrentUserService $currentUserService,
        private structureManager $structureManager,
    ) {
    }

    public function getProfile(): ?UserProfileRestDto
    {
        $element = $this->getCurrentUserElement();
        if ($element === null) {
            return null;
        }

        return new UserProfileRestDto(
            userName: (string)$element->userName,
            company: (string)$element->company,
            firstName: (string)$element->firstName,
            lastName: (string)$element->lastName,
            address: (string)$element->address,
            city: (string)$element->city,
            postIndex: (string)$element->postIndex,
            country: (string)$element->country,
            email: (string)$element->email,
            phone: (string)$element->phone,
            website: (string)$element->website,
            subscribe: (bool)$element->subscribe,
            showemail: (bool)$element->showemail,
        );
    }

    /**
     * @param array<string, mixed> $input
     */
    public function saveProfile(array $input): ?UserProfileRestDto
    {
        $element = $this->getCurrentUserElement();
        if ($element === null) {
            return null;
        }

        foreach (self::EDITABLE_FIELDS as $field) {
            if (array_key_exists($field, $input)) {
                $element->$field = (string)$input[$field];
            }
        }
        foreach (self::BOOL_FIELDS as $field) {
            if (array_key_exists($field, $input)) {
                $element->$field = !empty($input[$field]) ? 1 : 0;
            }
        }

        $element->persistElementData();

        return $this->getProfile();
    }

    private function getCurrentUserElement(): ?userElement
    {
        $user = $this->currentUserService->getCurrentUser();
        if (empty($user->id)) {
            return null;
        }
        $element = $this->structureManager->getElementById((int)$user->id);
        return $element instanceof userElement ? $element : null;
    }
}
