<div class='center_block'>
	{if $currentLanguage->getElementFromHeader('search')}
		{include file=$theme->template("search.header.tpl") element=$currentLanguage->getElementFromHeader('search')}
	{/if}
	{include file=$theme->template($currentLayout)}
</div>