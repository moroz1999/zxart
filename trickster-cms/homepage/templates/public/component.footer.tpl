{* Delete in actual project: example on how to get the module from footer modules*}
{*
{if $currentLanguage->getElementFromFooter('article')}
	{include file=$theme->template('article.footer.tpl') element=$currentLanguage->getElementFromFooter('article')}
{/if}
*}
<footer class='footer_block'>
	{*{if $currentLanguage->getElementFromFooter('selectedEvents')}*}
	{*{include file=$theme->template("selectedEvents.footer.tpl") element=$currentLanguage->getElementFromFooter('selectedEvents')}*}
	{*{/if}*}
	{*{include file=$theme->template("component.artweb.tpl")}*}
</footer>