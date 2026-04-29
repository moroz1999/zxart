<aside class="right_column">
	{foreach from=$currentLanguage->getRightColumnElementsList() item=columnElement}
		{include file=$theme->template($columnElement->getTemplate("column")) element=$columnElement}
	{/foreach}
</aside>