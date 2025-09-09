{foreach $element->getSupportedLanguagesMap() as $code => $title}
<a class="language-link" href="{$element->getCatalogueUrl(['languages' => $code])}"><span class="language-link-icon">{$element->getLanguageEmoji($code)}</span> {$title}</a>{if !$title@last}, {/if}
{/foreach}