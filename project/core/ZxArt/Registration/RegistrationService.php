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
    private const array USER_FIELDS = [
        'company', 'firstName', 'lastName', 'userName', 'password',
        'address', 'email', 'phone', 'city', 'postIndex', 'country', 'website',
    ];

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
        $usersElementId = $this->structureManager->getElementIdByMarker('users');
        $usersElement = $usersElementId ? $this->structureManager->getElementById($usersElementId) : null;
        if ($registration === null || !$usersElement instanceof structureElement) {
            throw new RegistrationException('Registration is not configured', 500);
        }

        $mainData = [];
        foreach (self::USER_FIELDS as $field) {
            if (array_key_exists($field, $request->fields)) {
                $mainData[$field] = (string)$request->fields[$field];
            }
        }
        $mainData['userName'] = $userName;
        $mainData['email'] = $email;
        $mainData['password'] = $request->password;
        $mainData['structureName'] = $userName;

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
        $registration->executeAction('sendEmail');

        return new RegistrationResultDto(true, $this->translate('userdata.registrationsuccess'));
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
