<?php

declare(strict_types=1);

namespace ZxArt\Parties\Services;

use translationsManager;

/**
 * Resolves a human-readable compo name from its raw compo key, per medium. Each medium stores compo
 * names in a different translation section. Falls back to the raw key when no translation exists.
 */
readonly class PartyCompoNameResolver
{
    private const array SECTIONS = [
        'prod' => 'party',
        'picture' => 'zxPicture',
        'music' => 'musiccompo',
    ];

    public function __construct(
        private translationsManager $translationsManager,
    ) {
    }

    public function resolve(string $medium, string $compoType): string
    {
        $section = self::SECTIONS[$medium] ?? '';
        if ($section === '') {
            return $compoType;
        }
        $translation = (string)$this->translationsManager->getTranslationByName(
            $section . '.compo_' . $compoType,
            null,
            false,
        );
        return $translation !== '' ? html_entity_decode($translation, ENT_QUOTES) : $compoType;
    }
}
