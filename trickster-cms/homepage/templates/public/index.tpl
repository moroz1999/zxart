<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml"
lang="{$currentLanguage->iso6391}" class="{$currentThemeClass|default:'dark-mode'}"
>
<head>
	{include file=$theme->template("index.head.meta.tpl")}
	{include file=$theme->template("index.head.seo.tpl")}
	{include file=$theme->template("index.head.opengraph.tpl")}
	{include file=$theme->template("index.head.twitter.tpl")}
	{include file=$theme->template("index.head.facebook.tpl")}
	{if $theme->templateExists("index.head.extra.tpl")}
		{include file=$theme->template("index.head.extra.tpl")}
	{/if}
	<link rel="shortcut icon" href="{$controller->baseURL}favicon.ico" />
	<link rel="stylesheet" type="text/css" href="{$controller->baseURL}css/set:{$theme->getCode()}/{if !empty($CSSFileName)}file:{$CSSFileName}.css{/if}" />
	{if !empty($jsScripts)}{foreach $jsScripts as $script}<script defer type="text/javascript" src="{$script}"></script>{/foreach}{/if}
	<link rel="alternate" type="application/rss+xml" href="{$controller->baseURL}rss/{$currentLanguage->iso6393}/" title="RSS" />
	{include file=$theme->template("index.head.language_links.tpl")}
	{if $currentElement instanceof LdJsonProviderInterface}{$currentElement->getLdJsonScriptHtml()}{/if}
	{if $currentLanguage->hidden}<meta name="robots" content="noindex">{/if}
</head>
<body data-page="{if $currentElement->structureType}{$currentElement->structureType|lower}{/if}" class="{if $currentLanguage->patternBackground} language_pattern_background{/if}{if $firstPageElement->requested}homepage{/if}" {if $currentLanguage->backgroundImage} style="background-image:url('{$controller->baseURL}image/type:background/id:{$currentLanguage->backgroundImage}/filename:{$currentLanguage->backgroundImageOriginalName}');{if $currentLanguage->patternBackground}background-repeat: repeat; background-size: auto{/if}"{/if}>

{include file=$theme->template("component.mainblock.tpl")}
{include file=$theme->template("javascript.data.tpl")}
{$googleAD = $configManager->get('google.ad')}
{if !empty($googleAD.ad_enabled) && $googleAD.ad_enabled}
	{include file=$theme->template("javascript.googlead.tpl")}
{/if}
{if $theme->templateExists("custom.JS.tpl")}
	{include file=$theme->template("custom.JS.tpl")}
{/if}
</body>
</html>