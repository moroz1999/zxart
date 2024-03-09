<!DOCTYPE html>
<html>
<head><meta name="robots" content="noindex" /></head>
<body>
{if $firstPageElement = $currentLanguage->getFirstPageElement()}<a href="{$firstPageElement->getUrl()}">ZX-Art</a>{/if}
{if count($languagesList)>1}
    {foreach $languagesList as $language}
        <a href='{$controller->baseURL}redirect/application:simple/type:language/element:{$currentElement->id}/code:{$language->iso6393}/'>{$language->title}</a>
    {/foreach}
    <br><br>
{/if}
{if $subMenuList = $currentLanguage->getElementFromHeader('subMenuList')}
    {foreach $subMenuList->getSubMenuList() as $subMenu}
        <a href="{$subMenu->URL}">{$subMenu->title}</a>
    {/foreach}
    <br><br>
{/if}
{include file=$theme->template("component.hr.tpl") symbol="="}
<br>
<br>
{include file=$theme->template("component.breadcrumbs.tpl")}
<br><br>
{include file=$theme->template("component.letters.tpl")}
{include file=$theme->template("component.years.tpl")}
<br><br>
{include file=$theme->template($currentElement->getTemplate()) element=$currentElement}
<style>
    body {
        color: #ccc;
        background: #2a2a2a;
        width: 80ch;
        font-family: monospace;
    }
    a {
        color: #83a9e7;
        text-decoration: none;
    }
    a::before{
        content: '\007B';
    }
    a::after{
        content: "\007D";
    }
</style>
</body>
</html>