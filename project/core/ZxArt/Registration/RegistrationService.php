<?php

declare(strict_types=1);

namespace ZxArt\Registration;

use App\Users\CurrentUserService;
use LanguagesManager;
use linksManager;
use registrationElement;
use structureElement;
use structureManager;
use translationsManager;
use userElement;
use ZxArt\Email\EmailValidationService;
use ZxArt\Registration\Dto\RegistrationRequestDto;
use ZxArt\Registration\Dto\RegistrationResultDto;
use ZxArt\Registration\Exception\RegistrationException;

readonly class RegistrationService
{
    public function __construct(
        private structureManager $structureManager,
        private LanguagesManager $languagesManager,
        private CurrentUserService $currentUserService,
        private EmailValidationService $emailValidationService,
        private linksManager $linksManager,
        private translationsManager $translationsManager,
    ) {
    }

    public function register(RegistrationRequestDto $request): RegistrationResultDto
    {
        $user = $this->currentUserService->getCurrentUser();
        if ($user->userName !== 'anonymous') {
            throw new RegistrationException('Already authenticated', 409);
        }

        $userName = trim($request->userName);
        $email = trim($request->email);
        if ($userName === '' || $email === '' || $request->password === '') {
            throw $this->validationException('userdata.bad_form_data');
        }
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw $this->validationException('userdata.bad_email');
        }
        if ($request->password !== $request->passwordRepeat) {
            throw $this->validationException('userdata.passwordsnotmatch');
        }
        if ($user->checkExistance($userName, $email)) {
            throw new RegistrationException($this->translate('userdata.userexists'), 409);
        }
        if (!$this->emailValidationService->isAllowed($email)) {
            throw $this->validationException('userdata.bad_email');
        }

        $registration = $this->getRegistrationElement();
        if ($registration === null) {
            throw new RegistrationException('Registration is not configured', 500);
        }
        // The users folder lives outside the public language subtree, so it is
        // only reachable when loaded directly under the registration element.
        $usersElementId = $this->structureManager->getElementIdByMarker('users');
        $usersElement = $usersElementId === null
            ? null
            : $this->structureManager->getElementById($usersElementId, $registration->getId(), true);
        if (!$usersElement instanceof structureElement) {
            throw new RegistrationException('Registration is not configured', 500);
        }

        $mainData = [
            'userName' => $userName,
            'email' => $email,
            'password' => $request->password,
            'structureName' => $userName,
        ];

        $userElement = $this->structureManager->createElement('user', 'show', $usersElement->getId());
        if (!$userElement instanceof userElement) {
            throw new RegistrationException('Could not create the account', 500);
        }
        $userElement->prepareActualData();
        if (!$userElement->importExternalData($mainData)) {
            throw $this->validationException('userdata.bad_form_data');
        }
        $userElement->persistElementData();

        foreach ($registration->getConnectedUserGroupsIds() as $groupId) {
            $this->linksManager->linkElements($groupId, $userElement->getId(), 'userRelation');
        }
        foreach ($mainData as $field => $value) {
            $registration->$field = $value;
        }
        $registration->dynamicFieldsData = $this->buildDynamicFieldsData($registration, $mainData);
        $registration->executeAction('sendEmail');

        return new RegistrationResultDto(true, $this->translate('userdata.registrationsuccess'));
    }

    /**
     * The verification email lists the registration form's connected fields, so
     * every field the account was created from gets its value by autocomplete role.
     *
     * @param array<string, string> $mainData
     * @return array<int, string>
     */
    private function buildDynamicFieldsData(registrationElement $registration, array $mainData): array
    {
        $values = [];
        foreach ($registration->getConnectedFields() as $field) {
            $role = $field->autocomplete;
            if (isset($mainData[$role])) {
                $values[$field->getId()] = $mainData[$role];
            }
        }
        return $values;
    }

    private function getRegistrationElement(): ?registrationElement
    {
        $elements = $this->structureManager->getElementsByType(
            'registration',
            $this->languagesManager->getCurrentLanguageId(),
        );
        foreach ($elements as $element) {
            if ($element instanceof registrationElement && $element->type === 'registration') {
                return $element;
            }
        }
        return null;
    }

    private function validationException(string $translationKey): RegistrationException
    {
        return new RegistrationException($this->translate($translationKey), 422);
    }

    private function translate(string $key): string
    {
        return (string)$this->translationsManager->getTranslationByName($key);
    }
}
