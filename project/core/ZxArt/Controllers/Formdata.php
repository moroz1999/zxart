<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use DbLoggableApplication;
use imageDataChunk;
use LanguagesManager;
use Monolog\Logger;
use Override;
use structureElement;
use structureManager;
use Throwable;
use translationsManager;
use ZxArt\ElementPrivileges\ElementPrivilegesService;
use ZxArt\Forms\FormCreateService;
use ZxArt\Forms\SubmittedFormFields;
use ZxArt\Forms\FormCreateException;
use ZxArt\Forms\FormValidationException;
use ZxArt\Forms\FormCreateType;
use ZxArt\Shared\EntityType;
use zxPictureElement;

/**
 * Generic read endpoint for entity edit and create forms (`/formdata/`).
 *
 * Existing entities are loaded by `id`; transient creation drafts are selected
 * by `entityType` and optional `year`. The corresponding element action checks
 * whether the current user may open the form.
 *
 * Response includes the localized entity title, raw form values, resolved
 * relations, display image URLs, and supporting options used by Angular forms.
 */
class Formdata extends LoggedControllerApplication
{
    use DbLoggableApplication;

    public $rendererName = 'json';

    /** Image preset for multi-file selector thumbnails; reads from the `releases` folder. */
    private const string SELECTOR_THUMBNAIL_PRESET = 'prodImage';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly structureManager $structureManager,
        private readonly ElementPrivilegesService $elementPrivilegesService,
        private readonly LanguagesManager $languagesManager,
        private readonly FormCreateService $formCreateService,
        private readonly translationsManager $translationsManager,
    ) {
        parent::__construct($controller, $logger);
    }

    #[Override]
    public function initialize(): void
    {
        $this->startSession('public');
        $this->createRenderer();
    }

    #[Override]
    public function execute($controller): void
    {
        $this->startDbLogging();

        $entityTypeValue = (string)$this->getParameter('entityType');
        if ($entityTypeValue !== '') {
            try {
                $formType = FormCreateType::tryFrom($entityTypeValue);
                if ($formType === null) {
                    $this->assignError('Unsupported form entity type', 400);
                } else {
                    $year = $this->getParameter('year') !== false ? (int)$this->getParameter('year') : null;
                    // the element the form was started from (an author, group, party
                    // or production); the form and its works are created under it
                    $parentId = $this->getParameter('parentId') !== false
                        ? (int)$this->getParameter('parentId')
                        : null;
                    if ($this->isCreateSubmission()) {
                        $this->formCreateService->submit(
                            $formType,
                            $year,
                            $this->controller,
                            $this->getCreateFields(),
                            $parentId,
                        );
                    } else {
                        $draft = $this->formCreateService->createDraft($formType, $year, $parentId);
                        $this->assignSuccess([
                            ...$this->buildFormData($draft, $this->getRefFields()),
                            'parent' => $this->buildParentRef($parentId),
                        ]);
                    }
                }
            } catch (FormValidationException $e) {
                $this->logThrowable('Formdata::execute create', $e);
                $this->assignError($e->getMessage(), 422);
            } catch (FormCreateException $e) {
                $this->logThrowable('Formdata::execute create', $e);
                $this->assignError($e->getMessage(), $e->getStatusCode());
            } catch (Throwable $e) {
                $this->logThrowable('Formdata::execute create', $e);
                $this->assignError('Internal server error', 500);
            }
        } else {
            try {
                $id = (int)$this->getParameter('id');
                if ($id <= 0) {
                    $this->assignError('Missing required parameter: id', 400);
                } else {
                    $element = $this->structureManager->getElementById($id);
                    if (!$element instanceof structureElement) {
                        $this->assignError('Element not found', 404);
                    } elseif (!$this->hasPrivilege($id, 'showPublicForm')) {
                        $this->assignError('Forbidden', 403);
                    } else {
                        $this->assignSuccess([
                            ...$this->buildFormData($element, $this->getRefFields()),
                            'parent' => $this->buildOwnerRef($element),
                        ]);
                    }
                }
            } catch (Throwable $e) {
                $this->logThrowable('Formdata::execute', $e);
                $this->assignError('Internal server error', 500);
            }
        }

        $this->renderer->display();
        $this->saveDbLog();
    }

    private function isCreateSubmission(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }

    /**
     * @return array<string, mixed>
     */
    private function getCreateFields(): array
    {
        /** @var array<string, array<string, mixed>> $uploadedFields */
        $uploadedFields = $_FILES['fields'] ?? [];
        /** @var array<string, mixed> $postedFields */
        $postedFields = $_POST['fields'] ?? [];

        return SubmittedFormFields::merge($uploadedFields, $postedFields);
    }

    /**
     * The element a creation form was started from, so the form can prefill the
     * field that element belongs in (the author of uploaded works, the party
     * they were released at, the category productions go to).
     *
     * @return array{id: int, title: string, structureType: string}|null
     */
    private function buildParentRef(?int $parentId): ?array
    {
        if ($parentId === null || $parentId <= 0) {
            return null;
        }
        // playlists and other elements outside the public tree need the fallback
        $parent = $this->structureManager->getElementById($parentId)
            ?? $this->structureManager->getElementById($parentId, null, true);
        if (!$parent instanceof structureElement) {
            return null;
        }

        return $this->buildRef($parent);
    }

    /**
     * The element an edited one belongs to (the production of an article or a
     * release), so the form can name it and can send the user there once the
     * element itself is gone.
     *
     * @return array{id: int, title: string, structureType: string}|null
     */
    private function buildOwnerRef(structureElement $element): ?array
    {
        $parent = $this->structureManager->getElementsFirstParent($element->getId());

        return $parent instanceof structureElement ? $this->buildRef($parent) : null;
    }

    /**
     * @return array{id: int, title: string, structureType: string}
     */
    private function buildRef(structureElement $element): array
    {
        return [
            'id' => (int)$element->getId(),
            'title' => $this->decode((string)$element->getTitle()),
            'structureType' => (string)$element->structureType,
        ];
    }

    /** @return string[] */
    private function getRefFields(): array
    {
        $refs = (string)$this->getParameter('refs');
        if ($refs === '') {
            return [];
        }
        return array_filter(array_map('trim', explode(',', $refs)));
    }

    /**
     * @param string[] $refFields
     * @return array{entityTitle: string, fields: array<string, mixed>, multilang: array<string, array<int, string>>, refs: array<string, array{id: int, title: string}>, multiRefs: array<string, list<array{id: int, title: string}>>, images: array<string, string>, files: array<string, string>, languages: list<array{id: int, name: string}>, categoriesTree: list<array{id: int, title: string, level: int, selected: bool}>, authorRefs: list<array{id: int, title: string}>, originalAuthorRefs: list<array{id: int, title: string}>, enums: array<string, list<array{value: string, label: string}>>, fileSelectors: array<string, list<array{id: int, title: string, isImage: bool, imageUrl: string|null}>>, aiStatuses: array<string, string>, splitGroups: list<array{group: string, items: list<array{key: string, label: string}>}>}
     */
    private function buildFormData(structureElement $element, array $refFields): array
    {
        $fields = [];
        $multilang = [];
        $refs = [];
        $multiRefs = [];
        $images = [];
        $files = [];
        $baseUrl = (string)$this->controller->baseURL;
        $multiLanguageFields = $element->getMultiLanguageFields();

        foreach ($element->getFormData() as $field => $value) {
            if (is_array($value)) {
                // relation lists (ConnectedElements) come back as arrays of
                // elements; resolve them to {id,title} for the multi-pickers and
                // keep the bare id list in `fields` for passthrough/save.
                $objects = array_filter($value, 'is_object');
                if ($objects !== []) {
                    $list = [];
                    $ids = [];
                    foreach ($objects as $item) {
                        if (!method_exists($item, 'getId')) {
                            continue;
                        }
                        $itemId = (int)$item->getId();
                        $ids[] = $itemId;
                        $title = method_exists($item, 'getSearchTitle')
                            ? (string)$item->getSearchTitle()
                            : (string)$item->title;
                        $list[] = ['id' => $itemId, 'title' => $this->decode($title)];
                    }
                    $multiRefs[$field] = $list;
                    $fields[$field] = $ids;
                    continue;
                }
                // multi-language fields ({languageId: value}); also exposed raw in
                // `fields` so forms can pass through non-multilang arrays (lists,
                // per-key maps) unchanged on save.
                if (isset($multiLanguageFields[$field])) {
                    // a text chunk holding nothing — every field of a creation
                    // draft — reports null, and the form takes one string per
                    // language: JS turns a null back into the string "null"
                    foreach ($value as $languageId => $languageValue) {
                        $value[$languageId] = (string)$languageValue;
                    }
                }
                $multilang[$field] = $value;
                $fields[$field] = $value;
                continue;
            }
            $fields[$field] = $value;
            $chunk = $element->getDataChunk($field);
            if ($chunk instanceof imageDataChunk) {
                if ($value !== '' && $value !== null) {
                    $images[$field] = $this->buildImageUrl($element, $field, (string)$value, $baseUrl);
                }
                continue;
            }
            if ($chunk instanceof \fileDataChunk) {
                // the persisted filename lives in `{field}Name` on the element
                // (fileName, trackerFileName, exeFileName, …)
                $name = (string)($element->{$field . 'Name'} ?? '');
                if ($name !== '') {
                    $files[$field] = $name;
                }
                continue;
            }
            if (in_array($field, $refFields, true) && is_numeric($value) && (int)$value > 0) {
                $ref = $this->structureManager->getElementById((int)$value);
                if ($ref instanceof structureElement) {
                    $refs[$field] = [
                        'id' => (int)$value,
                        'title' => html_entity_decode((string)$ref->title, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                    ];
                }
            }
        }

        return [
            'entityTitle' => $this->decode((string)$element->getTitle()),
            'fields' => $fields,
            'multilang' => $multilang,
            'refs' => $refs,
            'multiRefs' => $multiRefs,
            'images' => $images,
            'files' => $files,
            'languages' => $this->buildLanguages(),
            'members' => $this->buildMembers($element),
            'roles' => method_exists($element, 'getAuthorRoles') ? array_values($element->getAuthorRoles()) : [],
            'subgroups' => $this->buildSubgroups($element),
            'categoriesTree' => $this->buildCategoriesTree($element),
            'authorRefs' => $this->buildAuthorRefs($element),
            'originalAuthorRefs' => $this->buildConnectedRefs($element, 'getOriginalAuthorsList'),
            'enums' => $this->buildEnums($element),
            'fileSelectors' => $this->buildFileSelectors($element),
            'aiStatuses' => $this->buildAiStatuses($element),
            'splitGroups' => $this->buildSplitGroups($element),
        ];
    }

    /**
     * Thumbnail of a stored image field. The main file of a picture is a native
     * ZX Spectrum screen that no browser can render, so it is shown through the
     * picture's own converted image URL. Every other field holds a regular image
     * and is served by the `adminImage` preset.
     */
    private function buildImageUrl(structureElement $element, string $field, string $value, string $baseUrl): string
    {
        if ($field === 'image' && $element instanceof zxPictureElement) {
            return $element->getImageUrl();
        }

        return $baseUrl . 'image/type:adminImage/id:' . $value
            . '/filename:' . rawurlencode((string)$element->originalName);
    }

    /**
     * Splittable items grouped for the prod split form (`getSplitData()` returns
     * [group => [key => label]]). Each checked item is split off into a new prod.
     *
     * @return list<array{group: string, items: list<array{key: string, label: string}>}>
     */
    private function buildSplitGroups(structureElement $element): array
    {
        if (!method_exists($element, 'getSplitData')) {
            return [];
        }
        $groups = [];
        foreach ($element->getSplitData() as $groupKey => $itemsGroup) {
            $items = [];
            foreach ((array)$itemsGroup as $key => $label) {
                $items[] = ['key' => (string)$key, 'label' => $this->decode((string)$label)];
            }
            $groups[] = ['group' => (string)$groupKey, 'items' => $items];
        }
        return $groups;
    }

    /**
     * Current AI queue status per re-queue field, for the AI form (prod/press).
     *
     * A creation draft is not persisted yet and its transient identifier is not
     * an element id, so it can hold no queue records.
     *
     * @return array<string, string>
     */
    private function buildAiStatuses(structureElement $element): array
    {
        if (!method_exists($element, 'getQueueStatus') || !$element->hasActualStructureInfo()) {
            return [];
        }
        $map = match ((string)$element->structureType) {
            'zxProd' => [
                'aiRestartSeo' => \ZxArt\Queue\QueueType::AI_SEO,
                'aiRestartIntro' => \ZxArt\Queue\QueueType::AI_INTRO,
                'aiRestartCategories' => \ZxArt\Queue\QueueType::AI_CATEGORIES_TAGS,
            ],
            'pressArticle' => [
                'aiRestartFix' => \ZxArt\Queue\QueueType::AI_PRESS_FIX,
                'aiRestartTranslate' => \ZxArt\Queue\QueueType::AI_PRESS_TRANSLATE,
                'aiRestartParse' => \ZxArt\Queue\QueueType::AI_PRESS_PARSE,
                'aiRestartSeo' => \ZxArt\Queue\QueueType::AI_PRESS_SEO,
            ],
            default => [],
        };
        $statuses = [];
        foreach ($map as $field => $queueType) {
            $statuses[$field] = (string)$element->getQueueStatus($queueType);
        }
        return $statuses;
    }

    /**
     * Existing files of each multi-file selector (screenshots, inlays, …) for
     * the file-selector manager. Uploads go back through `publicReceive` →
     * `receiveFiles`; deletes hit the shared `delete` action per file element.
     *
     * Thumbnails use the `prodImage` preset: selector files belong to prods and
     * releases and are therefore stored in the `releases` folder, which is where
     * that preset reads from. A native ZX screen ignores the preset and renders
     * through its own converter; a PC screenshot (png, jpg, bmp) is resized by
     * the preset, so a preset pointing at the default uploads folder would leave
     * every PC screenshot without a thumbnail.
     *
     * @return array<string, list<array{id: int, title: string, isImage: bool, imageUrl: string|null}>>
     */
    private function buildFileSelectors(structureElement $element): array
    {
        if (!method_exists($element, 'getFileSelectorPropertyNames') || !method_exists($element, 'getFilesList')) {
            return [];
        }
        $selectors = [];
        foreach ($element->getFileSelectorPropertyNames() as $propertyName) {
            $items = [];
            foreach ($element->getFilesList($propertyName) as $file) {
                if (!is_object($file) || !method_exists($file, 'getId')) {
                    continue;
                }
                $isImage = method_exists($file, 'isImage') && $file->isImage();
                $items[] = [
                    'id' => (int)$file->getId(),
                    'title' => $this->decode((string)$file->title),
                    'isImage' => $isImage,
                    'imageUrl' => $isImage && method_exists($file, 'getImageUrl')
                        ? (string)$file->getImageUrl(self::SELECTOR_THUMBNAIL_PRESET)
                        : null,
                ];
            }
            $selectors[$propertyName] = $items;
        }
        return $selectors;
    }

    /**
     * Backend-driven select options ({value,label}) for enum fields, with labels
     * already translated to the current language (so the SPA needs no extra i18n
     * keys). Fixed enums (border colour, rotation) stay hardcoded in Angular.
     * A `clientLabels` spec sends the bare values instead, for lists the SPA
     * translates itself (language and hardware codes).
     *
     * @return array<string, list<array{value: string, label: string}>>
     */
    private function buildEnums(structureElement $element): array
    {
        $specs = $this->enumSpecs((string)$element->structureType);
        if (!$specs) {
            return [];
        }
        $enums = [];
        foreach ($specs as $field => $spec) {
            if (!method_exists($element, $spec['method'])) {
                continue;
            }
            $options = [];
            if (isset($spec['emptyLabelKey'])) {
                $options[] = [
                    'value' => '',
                    'label' => (string)$this->translationsManager->getTranslationByName($spec['emptyLabelKey']),
                ];
            } elseif (!empty($spec['emptyBlank'])) {
                $options[] = ['value' => '', 'label' => ''];
            }
            $rawItems = $element->{$spec['method']}();
            $mode = $spec['mode'] ?? 'list';
            if ($mode === 'grouped') {
                // method returns [groupName => [items]] — flatten to a plain list
                $flat = [];
                foreach ($rawItems as $group) {
                    foreach ((array)$group as $item) {
                        $flat[] = $item;
                    }
                }
                $rawItems = $flat;
                $mode = 'list';
            }
            foreach ($rawItems as $key => $item) {
                if (!empty($spec['clientLabels'])) {
                    // the SPA owns these labels; the backend only names the values
                    $value = (string)($mode === 'map' ? $key : $item);
                    $options[] = ['value' => $value, 'label' => $value];
                    continue;
                }
                if ($mode === 'map') {
                    // method returns [value => translationKey]
                    $value = (string)$key;
                    $label = (string)$this->translationsManager->getTranslationByName((string)$item);
                } else {
                    $value = (string)$item;
                    $labelKey = !empty($spec['stripDots']) ? str_replace('.', '', $value) : $value;
                    $label = (string)$this->translationsManager->getTranslationByName($spec['prefix'] . $labelKey);
                }
                $options[] = ['value' => $value, 'label' => $label !== '' ? $label : $value];
            }
            $enums[$field] = $options;
        }
        return $enums;
    }

    /**
     * Per-entity enum field definitions (option source method + label strategy).
     *
     * @return array<string, array{method: string, mode?: string, prefix?: string, stripDots?: bool, emptyBlank?: bool, emptyLabelKey?: string, clientLabels?: bool}>
     */
    private function enumSpecs(string $structureType): array
    {
        return match ($structureType) {
            'zxMusic' => [
                'compo' => ['method' => 'getCompoTypes', 'prefix' => 'musiccompo.compo_', 'emptyBlank' => true],
                'formatGroup' => ['method' => 'getFormatGroups', 'clientLabels' => true],
                'chipType' => ['method' => 'getChipTypes', 'prefix' => 'zxmusic.chiptype_', 'emptyLabelKey' => 'zxmusic.chiptype_default'],
                'channelsType' => ['method' => 'getChannelsTypes', 'prefix' => 'zxmusic.channelstype_', 'emptyLabelKey' => 'zxmusic.channelstype_default'],
                'frequency' => ['method' => 'getFrequencies', 'prefix' => 'zxmusic.frequency_', 'emptyLabelKey' => 'zxmusic.frequency_default'],
                'intFrequency' => ['method' => 'getIntFrequencies', 'prefix' => 'zxmusic.intfrequency_', 'stripDots' => true, 'emptyLabelKey' => 'zxmusic.frequency_default'],
            ],
            'musicUploadForm' => [
                'compo' => ['method' => 'getCompoTypes', 'prefix' => 'musiccompo.compo_', 'emptyBlank' => true],
                'formatGroup' => ['method' => 'getFormatGroups', 'clientLabels' => true],
                'chipType' => ['method' => 'getChipTypes', 'prefix' => 'zxmusic.chiptype_', 'emptyLabelKey' => 'zxmusic.chiptype_default'],
                'channelsType' => ['method' => 'getChannelsTypes', 'prefix' => 'zxmusic.channelstype_', 'emptyLabelKey' => 'zxmusic.channelstype_default'],
                'frequency' => ['method' => 'getFrequencies', 'prefix' => 'zxmusic.frequency_', 'emptyLabelKey' => 'zxmusic.frequency_default'],
                'intFrequency' => ['method' => 'getIntFrequencies', 'prefix' => 'zxmusic.intfrequency_', 'stripDots' => true, 'emptyLabelKey' => 'zxmusic.frequency_default'],
            ],
            'zxProd' => [
                'compo' => ['method' => 'getCompoTypes', 'prefix' => 'party.compo_', 'emptyBlank' => true],
                'language' => ['method' => 'getLanguageCodes', 'clientLabels' => true],
            ],
            'zxProdsUploadForm' => [
                'compo' => ['method' => 'getCompoTypes', 'prefix' => 'party.compo_', 'emptyBlank' => true],
                'language' => ['method' => 'getLanguageCodes', 'clientLabels' => true],
            ],
            'zxPicture' => [
                'compo' => ['method' => 'getCompoTypes', 'prefix' => 'zxPicture.compo_', 'emptyBlank' => true],
                'palette' => ['method' => 'getPaletteTypes', 'prefix' => 'zxpicture.palette_', 'emptyLabelKey' => 'zxpicture.palette_auto'],
                'type' => ['method' => 'getZxPictureTypes', 'mode' => 'map'],
            ],
            'picturesUploadForm' => [
                'compo' => ['method' => 'getCompoTypes', 'prefix' => 'zxPicture.compo_', 'emptyBlank' => true],
                'palette' => ['method' => 'getPaletteTypes', 'prefix' => 'zxpicture.palette_', 'emptyLabelKey' => 'zxpicture.palette_auto'],
                'type' => ['method' => 'getZxPictureTypes', 'mode' => 'map'],
            ],
            'zxRelease' => [
                'releaseType' => ['method' => 'getReleaseTypes', 'prefix' => 'zxRelease.type_'],
                'releaseFormat' => ['method' => 'getReleaseFormats', 'prefix' => 'zxRelease.filetype_'],
                'language' => ['method' => 'getLanguageCodes', 'clientLabels' => true],
                'hardwareRequired' => ['method' => 'getHardwareList', 'mode' => 'grouped', 'clientLabels' => true],
            ],
            default => [],
        };
    }

    /**
     * Flat connected-author list ({id,title}) for entities that store authorship
     * as a plain `author` list (tune, picture) rather than role-based members.
     * Like categories, this is link-backed and absent from getFormData(), so the
     * form must reload and round-trip it.
     *
     * @return list<array{id: int, title: string}>
     */
    private function buildAuthorRefs(structureElement $element): array
    {
        return $this->buildConnectedRefs($element, 'getAuthorsList');
    }

    /**
     * Resolve a link-backed element list (returned by `$method`) to {id,title}.
     * Such lists live in the links table and are absent from getFormData(), so
     * the form must reload and round-trip them (see categories/author trap).
     *
     * @return list<array{id: int, title: string}>
     */
    private function buildConnectedRefs(structureElement $element, string $method): array
    {
        if (!method_exists($element, $method)) {
            return [];
        }
        $refs = [];
        foreach ($element->$method() as $item) {
            if (!is_object($item) || !method_exists($item, 'getId')) {
                continue;
            }
            $title = method_exists($item, 'getSearchTitle')
                ? (string)$item->getSearchTitle()
                : (string)$item->title;
            $refs[] = ['id' => (int)$item->getId(), 'title' => $this->decode($title)];
        }
        return $refs;
    }

    /**
     * Full prod-category tree with the element's current selection. The
     * connected categories are the element's structural parents, so the form
     * must round-trip them on save (an empty `categories` post wipes the links
     * and orphans the element).
     *
     * @return list<array{id: int, title: string, level: int, selected: bool}>
     */
    private function buildCategoriesTree(structureElement $element): array
    {
        if (!method_exists($element, 'getCategoriesSelectorInfo')) {
            return [];
        }
        $tree = [];
        foreach ($element->getCategoriesSelectorInfo() as $info) {
            $tree[] = [
                'id' => (int)$info['id'],
                'title' => $this->decode((string)$info['title']),
                'level' => (int)$info['level'],
                'selected' => (bool)$info['selected'],
            ];
        }
        return $tree;
    }

    /** @return list<array{id: int, title: string, startDate: string, endDate: string, roles: list<string>}> */
    private function buildMembers(structureElement $element): array
    {
        // authorship is keyed by EntityType, which differs from structureType for
        // some entities (zxProd→Prod, …) and aliases store it under their parent type
        $entityType = match ((string)$element->structureType) {
            'author', 'authorAlias' => EntityType::Author,
            'group', 'groupAlias' => EntityType::Group,
            'party' => EntityType::Party,
            'zxProd' => EntityType::Prod,
            'zxRelease' => EntityType::Release,
            'zxPicture' => EntityType::Picture,
            'zxMusic' => EntityType::Tune,
            default => null,
        };
        if ($entityType === null || !method_exists($element, 'getAuthorsInfo')) {
            return [];
        }
        $members = [];
        foreach ($element->getAuthorsInfo($entityType) as $info) {
            $author = $info['authorElement'] ?? null;
            if (!is_object($author)) {
                continue;
            }
            $members[] = [
                'id' => (int)($info['authorId'] ?? 0),
                'title' => $this->decode((string)$author->title),
                'startDate' => (string)($info['startDate'] ?? ''),
                'endDate' => (string)($info['endDate'] ?? ''),
                'roles' => array_values($info['roles'] ?? []),
            ];
        }
        return $members;
    }

    /** @return list<array{id: int, title: string}> */
    private function buildSubgroups(structureElement $element): array
    {
        if (!method_exists($element, 'getSubGroupIds')) {
            return [];
        }
        $subgroups = [];
        foreach ($element->getSubGroupIds() as $subGroupId) {
            $subGroup = $this->structureManager->getElementById((int)$subGroupId);
            if (is_object($subGroup)) {
                $subgroups[] = ['id' => (int)$subGroupId, 'title' => $this->decode((string)$subGroup->title)];
            }
        }
        return $subgroups;
    }

    private function decode(string $value): string
    {
        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /** @return list<array{id: int, name: string}> */
    private function buildLanguages(): array
    {
        $languages = [];
        foreach ($this->languagesManager->getLanguagesList() as $language) {
            $languages[] = [
                'id' => (int)$language->id,
                'name' => html_entity_decode((string)$language->title, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            ];
        }
        return $languages;
    }

    private function hasPrivilege(int $id, string $privilege): bool
    {
        return $this->elementPrivilegesService->getPrivileges($id, [$privilege])->privileges[$privilege] ?? false;
    }

    private function assignSuccess(mixed $data): void
    {
        $this->renderer->assign('body', $data);
    }

    private function assignError(string $message, int $statusCode = 500): void
    {
        CmsHttpResponse::getInstance()->setStatusCode((string)$statusCode);
        $this->renderer->assign('body', ['errorMessage' => $message]);
    }

    #[Override]
    public function getUrlName(): string
    {
        return '';
    }
}
