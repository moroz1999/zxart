{if !empty($languageLinks)}
    {foreach $languageLinks as $code=>$languageLink}
        <link rel="alternate" hreflang="{$code}" href="{$languageLink}"/>
    {/foreach}
{/if}