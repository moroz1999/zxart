<?php

use App\Users\CurrentUserService;

abstract class structureElementAction extends errorLogger implements DependencyInjectionContextInterface
{
    use DependencyInjectionContextTrait;

    protected $loggable = false;
    public $actionName = false;
    protected $validated = true;
    public $expectedFields = [];
    public $validators = [];
    /**
     * @var structureElement $structureElement
     */
    public $structureElement;
    protected $elementFormData;

    public function __construct()
    {
    }

    public function getPrivilegeName()
    {
        return $this->actionName;
    }

    public function processFormData()
    {
        if (method_exists($this, 'setExpectedFields')) {
            $this->setExpectedFields($this->expectedFields);
        }
        if (count($this->expectedFields) > 0) {
            $controller = controller::getInstance();
            if ($elementFormData = $controller->getElementFormData($this->structureElement->id)) {
                $this->elementFormData = $elementFormData;
                if (method_exists($this, 'setValidators')) {
                    $this->setValidators($this->validators);
                }
                $this->validated = $this->structureElement->importExternalData($elementFormData, $this->expectedFields, $this->validators);
            }
        }
    }

    public function startAction()
    {
        if (method_exists($this, 'getExtraModuleFields')) {
            if ($moduleFields = $this->getExtraModuleFields()) {
                $this->structureElement->addModuleFields($moduleFields);
            }
        }
        $this->processFormData();

        $controller = controller::getInstance();
        $structureManager = $this->getService(structureManager::class);

        if ($this->loggable) {
            $this->logAction();
        }

        $this->execute($structureManager, $controller, $this->structureElement);
    }

    protected function getTranslations($marker = 'public_translations', $languageId = false)
    {
        $translations = false;
        if (!$languageId) {
            $languagesManager = $this->getService(LanguagesManager::class);
            $languageId = $languagesManager->getCurrentLanguageId();
        }
        if ($languageId) {
            $translationManager = $this->getService(translationsManager::class);
            $translations = $translationManager->getTranslationsList($marker, $languageId);
        }
        return $translations;
    }

    abstract public function execute(
        structureManager $structureManager,
        controller $controller,
        structureElement $structureElement,
    ): void;

    protected function logAction()
    {
        $collection = persistableCollection::getInstance('actions_log');
        $record = $collection->getEmptyObject();
        $currentUserService = $this->getService(CurrentUserService::class);
        $user = $currentUserService->getCurrentUser();

        $record->elementId = $this->structureElement->id;
        $record->elementType = $this->structureElement->structureType;
        $record->elementName = $this->structureElement->structureName;
        $record->action = $this->actionName;
        $record->userId = $user->id;
        $record->userIP = $user->IP;
        $record->userName = $user->userName;
        $record->date = time();
        $record->persist();
    }

    protected function getErrorLogLocation(): string
    {
        return $this->structureElement->title . " " . $this->structureElement->structureType . " " . $this->structureElement->id;
    }

    /**
     * Response after a successful form save. On a JSON/ajax request (the SPA
     * forms, run through `/ajax/`) it returns a bare `{"id": <id>}` body (200) —
     * the Angular app navigates to that entity and refreshes; also covers create,
     * where the id is the newly created element. On a legacy full-page request it
     * performs the redirect. Public form `*-receive` actions call this instead of
     * `$controller->redirect()`.
     */
    protected function respondFormSaved(controller $controller, structureElement $structureElement): void
    {
        $renderer = $this->getService(renderer::class);
        if ($renderer instanceof RendererPluginAppendInterface) {
            $renderer->assign('body', ['id' => $structureElement->getId()]);
            return;
        }
        $controller->redirect($structureElement->URL);
    }
}



