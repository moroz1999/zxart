<!DOCTYPE html>
<html class="{$currentThemeClass|default:'dark-mode'}">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<base href="/" />
	{if $theme->templateExists("index.head.extra.tpl")}
		{include file=$theme->template("index.head.extra.tpl")}
	{/if}
	<link rel="shortcut icon" href="{$controller->baseURL}favicon.ico" />
	<link rel="stylesheet" type="text/css" href="{$controller->baseURL}css/set:{$theme->getCode()}/{if !empty($CSSFileName)}file:{$CSSFileName}.css{/if}" />
</head>
<body>
<zx-spa-root></zx-spa-root>
</body>
</html>
