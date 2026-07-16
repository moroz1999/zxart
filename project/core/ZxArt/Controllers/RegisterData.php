<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use App\Users\CurrentUserService;
use CmsHttpResponse;
use controller;
use LanguagesManager;
use linksManager;
use Monolog\Logger;
use registrationElement;
use structureElement;
use structureManager;
use Throwable;
use translationsManager;
use userElement;
use ZxArt\Email\EmailValidationService;

/**
 * Static self-service registration endpoint (`/register-data/`, POST). Creates a
 * new account from a fixed set of fields (no dynamic registration fields) and
 * sends the verification email, mirroring the legacy `submitRegistration` flow.
 * Named *RegisterData* so `/register` stays free for the SPA page.
 */
class RegisterData extends LoggedControllerApplication
{
    public $rendererName = 'json';

    /** User-element fields a static registration may set. */
    private const USER_FIELDS = [
        'company', 'firstName', 'lastName', 'userName', 'password',
        'address', 'email', 'phone', 'city', 'postIndex', 'country', 'website',
    ];

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly structureManager $structureManager,
        private readonly LanguagesManager $languagesManager,
        private readonly CurrentUserService $currentUserService,
        private readonly EmailValidationService $emailValidationService,
    ) {
        parent::__construct($controller, $logger);
    }

    public function initialize(): void
    {
        $this->startSession('public');
        $this->createRenderer();
    }

    public function execute($controller): void
    {
        try {
            $body = json_decode((string)file_get_contents('php://input'), true);
            $this->register(is_array($body) ? $body : []);
        } catch (Throwable $e) {
            $this->logThrowable('RegisterData::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    /** @param array<string, mixed> $input */
    private function register(array $input): void
    {
        $tm = $this->getService(translationsManager::class);

        $user = $this->currentUserService->getCurrentUser();
        if ($user->userName !== 'anonymous') {
            $this->assignError('Already authenticated', 400);
            return;
        }

        $userName = trim((string)($input['userName'] ?? ''));
        $email = trim((string)($input['email'] ?? ''));
        $password = (string)($input['password'] ?? '');
        $passwordRepeat = (string)($input['passwordRepeat'] ?? '');

        if ($userName === '' || $email === '' || $password === '') {
            $this->assignFail((string)$tm->getTranslationByName('userdata.bad_form_data'));
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->assignFail((string)$tm->getTranslationByName('userdata.bad_email'));
            return;
        }
        if ($password !== $passwordRepeat) {
            $this->assignFail((string)$tm->getTranslationByName('userdata.passwordsnotmatch'));
            return;
        }
        if ($user->checkExistance($userName, $email)) {
            $this->assignFail((string)$tm->getTranslationByName('userdata.userexists'));
            return;
        }
        if (!$this->emailValidationService->isAllowed($email)) {
            $this->assignFail((string)$tm->getTranslationByName('userdata.bad_email'));
            return;
        }

        $registration = $this->getRegistrationElement();
        $usersElementId = $this->structureManager->getElementIdByMarker('users');
        $usersElement = $usersElementId ? $this->structureManager->getElementById($usersElementId) : null;
        if ($registration === null || !$usersElement instanceof structureElement) {
            $this->assignError('Registration is not configured', 500);
            return;
        }

        $mainData = [];
        foreach (self::USER_FIELDS as $field) {
            if (array_key_exists($field, $input)) {
                $mainData[$field] = (string)$input[$field];
            }
        }
        $mainData['userName'] = $userName;
        $mainData['email'] = $email;
        $mainData['password'] = $password;
        $mainData['structureName'] = $userName;

        $userElement = $this->structureManager->createElement('user', 'show', $usersElement->getId());
        if (!$userElement instanceof userElement) {
            $this->assignError('Could not create the account', 500);
            return;
        }
        $userElement->prepareActualData();
        if (!$userElement->importExternalData($mainData)) {
            $this->assignFail((string)$tm->getTranslationByName('userdata.bad_form_data'));
            return;
        }
        $userElement->persistElementData();

        // assign the registration's default user groups
        $linksManager = $this->getService(linksManager::class);
        foreach ($registration->getConnectedUserGroupsIds() as $groupId) {
            $linksManager->linkElements($groupId, $userElement->getId(), 'userRelation');
        }

        // send the verification email through the registration element (mirrors submit)
        foreach ($mainData as $field => $value) {
            $registration->$field = $value;
        }
        $registration->executeAction('sendEmail');

        $this->renderer->assign('body', [
            'success' => true,
            'message' => (string)$tm->getTranslationByName('userdata.registrationsuccess'),
        ]);
    }

    private function getRegistrationElement(): ?registrationElement
    {
        $elements = $this->structureManager->getElementsByType('registration', $this->languagesManager->getCurrentLanguageId());
        foreach ($elements as $element) {
            if ($element instanceof registrationElement && $element->type === 'registration') {
                return $element;
            }
        }
        return null;
    }

    private function assignFail(string $message): void
    {
        $this->renderer->assign('body', ['success' => false, 'message' => $message]);
    }

    private function assignError(string $message, int $statusCode = 500): void
    {
        CmsHttpResponse::getInstance()->setStatusCode((string)$statusCode);
        $this->renderer->assign('body', ['errorMessage' => $message]);
    }

    public function getUrlName(): string
    {
        return '';
    }
}
