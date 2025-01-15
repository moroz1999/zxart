{foreach $element->getSupportedLanguagesMap() as $code => $title}
    <a href="{$element->getCatalogueUrl(['languages' => $code])}">{$title}</a>{if !$title@last}, {/if}
{/foreach}