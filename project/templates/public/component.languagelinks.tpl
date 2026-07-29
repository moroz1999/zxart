{foreach $element->getSupportedLanguagesMap() as $code => $title}
<a class="language-link" href="{$element->getCatalogueUrl(['languages' => $code])}">{$title}</a>{if !$title@last}, {/if}
{/foreach}
