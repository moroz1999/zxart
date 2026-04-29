{if count($languagesList)>1}
{stripdomspaces}
	<div class='languages_block'>
        {foreach $languagesList as $language}
            {if !empty($languageLinks[$language->iso6391])}
                {$url = $languageLinks[$language->iso6391]}
            {else}
                {$url = $language->getFirstPageElement()->getUrl()}
            {/if}
            <a class='language_item{if $language->requested} language_active{/if}' href='{$url}' title="{$language->title}">
                {$language->title}
            </a>
        {/foreach}
	</div>
{/stripdomspaces}
{/if}