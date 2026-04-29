{capture assign="moduleContent"}
	{if $element->title}
		{capture assign='moduleTitle'}
			{$element->title}
		{/capture}
	{/if}
	<span class="currencies_block_label">{translations name='currencies.selectcurrency'}:</span>
	{foreach from=$element->getCurrenciesList() item=currency name=currencies}
		<a class='currency_item{if $currency->active} currency_active{/if}' href='{$currency->URL}' title="{$currency->title}">{$currency->code}</a>
	{/foreach}
{/capture}

{assign moduleClass "currency_block"}
{assign moduleTitleClass "currency_block_title"}
{assign moduleContentClass "currencies_block"}
{include file=$theme->template("component.columnmodule.tpl")}