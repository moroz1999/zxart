<aside class="left_column">
	{foreach from=$currentLanguage->getLeftColumnElementsList() item=columnElement}
		{if ($columnElement)}
			{include file=$theme->template($columnElement->getTemplate("column")) element=$columnElement}
		{/if}
	{/foreach}
</aside>