<header class='header_block'>
	{include file=$theme->template("component.logo.tpl")}
	{include file=$theme->template("component.languages.tpl")}

	{if $subMenuList = $currentLanguage->getElementFromHeader('subMenuList')}
		{include file=$theme->template("subMenuList.header.tpl") element=$subMenuList}
	{/if}

	{* Delete in actual project: example on how to get the module from header modules*}
	{*
	{if $currentLanguage->getElementFromHeader('article')}
		{include file=$theme->template('article.header.tpl') element=$currentLanguage->getElementFromHeader('article')}
	{/if}
	*}
</header>
{if $headerGallery=$currentLanguage->getElementFromHeader('gallery')}
	{include file=$theme->template('gallery.header.tpl') element=$headerGallery}
{/if}