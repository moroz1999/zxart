<div class="header_languageselector">
	{foreach from=$languagesList item=language}
	{if !($controller->getParameter('lang'))}
		{$new_lang_path = "lang:{$language->iso6393}/"}
	{else}
		{$new_lang_path = ''}
		{if $controller->getParameter('lang') != $language->iso6393}
			{$new_lang_path = "lang:{$language->iso6393}/"}
		{/if}
	{/if}
		<a href="{$currentFullUrl}{$new_lang_path}" class="header_languageselector_item header_languageselector_item_{$language->iso6393} {if $language->id == $currentLanguageId} header_languageselector_item_current{/if}" title="{$language->title}"></a>
	{/foreach}
</div>