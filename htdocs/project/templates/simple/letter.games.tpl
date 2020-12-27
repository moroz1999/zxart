{if $element->title}{include file=$theme->template('component.heading.tpl') value=$element->title}{/if}
{foreach from=$currentElement->getGamesList() item=game}
	{include file=$theme->template('game.short.tpl') element=$game}
{/foreach}