{if $element->title}{include file=$theme->template('component.heading.tpl') value=$element->title}{/if}
{if $itemsList = $element->getItemsList()}
	{foreach from=$itemsList item=picture}{include file=$theme->template("zxPicture.short.tpl") element=$picture}{/foreach}
{/if}
{include file=$theme->template("component.hr.tpl") symbol="-"}<br><br><br>