<?php

use App\Users\CurrentUserService;
use ZxArt\Press\Repositories\PressArticleRepository;
use ZxArt\Shared\StructureType;

class publicReceivePressArticle extends structureElementAction
{
    protected $loggable = true;

    /** Rights the author of a new article keeps over it. */
    private const array AUTHOR_ACTIONS = ['showPublicForm', 'publicReceive', 'publicDelete'];

    /**
     * @param pressArticleElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            if ($structureElement->structureName == '') {
                $structureElement->structureName = $structureElement->title;
            }
            $isNewArticle = !$structureElement->hasActualStructureInfo();
            // the form data is keyed by the identifier the element has right now,
            // which persisting replaces with the assigned one
            $submittedOriginalContent = $this->getSubmittedOriginalContent($controller, $structureElement->getIdentifier());
            $structureElement->persistElementData();
            if ($isNewArticle) {
                $this->grantAuthorPrivileges($structureElement);
            }
            if ($submittedOriginalContent !== null) {
                $pressArticleRepository = $this->getService(PressArticleRepository::class);
                $pressArticleRepository->saveOriginalContent($structureElement->getId(), $submittedOriginalContent);
            }

            if ($parentElement = $structureElement->getFirstParentElement()) {
                $linksManager = $this->getService(linksManager::class);
                $linksManager->unLinkElements($parentElement->getId(), $structureElement->getId());
                $linksManager->linkElements($parentElement->getId(), $structureElement->getId(), 'prodArticle');
            }

            $this->respondFormSaved($controller, $structureElement); return;
        }

        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields): void
    {
        $expectedFields = [
            'title',
            'externalLink',
            'authors',
            'people',
            'software',
            'groups',
            'parties',
            'tunes',
            'pictures',
            'introduction',
            'content',
            'originalContent',
            'allowComments',
        ];
    }

    public function setValidators(&$validators): void
    {
    }

    /**
     * The archived original is the source the AI formats, translates and shortens,
     * so it is kept exactly as it was pasted: taken from the submitted data rather
     * than from the html chunk, which purifies what goes through it. Null when the
     * form carried no original at all — the archived text stays untouched then.
     */
    private function getSubmittedOriginalContent(controller $controller, string $elementIdentifier): ?string
    {
        $formData = $controller->getElementFormData($elementIdentifier);
        $submitted = is_array($formData) ? $formData['originalContent'] ?? null : null;

        return is_string($submitted) ? $submitted : null;
    }

    /**
     * The user who added the article keeps control over it — the form, saving and
     * deletion — the same way a release grants them to its uploader.
     */
    private function grantAuthorPrivileges(structureElement $structureElement): void
    {
        $privilegesManager = $this->getService(privilegesManager::class);
        $user = $this->getService(CurrentUserService::class)->getCurrentUser();
        foreach (self::AUTHOR_ACTIONS as $action) {
            $privilegesManager->setPrivilege(
                $user->id,
                $structureElement->getId(),
                StructureType::PressArticle->value,
                $action,
                'allow',
            );
        }
        $user->refreshPrivileges();
    }
}

