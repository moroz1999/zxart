<?php

declare(strict_types=1);

namespace ZxArt\Forms;

use LanguagesManager;
use ZxArt\Forms\Dto\FormLanguageDto;

/**
 * The interface languages a form edits a multi-language field in, in the site's
 * own order.
 *
 * Every endpoint that answers a form with per-language values sends this list
 * beside them, so the form can label one input per language.
 */
final readonly class FormLanguagesProvider
{
    public function __construct(
        private LanguagesManager $languagesManager,
    ) {
    }

    /**
     * @return list<FormLanguageDto>
     */
    public function getLanguages(): array
    {
        $languages = [];
        /** @var object{id: int|string, title: string} $language */
        foreach ($this->languagesManager->getLanguagesList() as $language) {
            $languages[] = new FormLanguageDto(
                id: (int)$language->id,
                name: html_entity_decode($language->title, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            );
        }

        return $languages;
    }
}
