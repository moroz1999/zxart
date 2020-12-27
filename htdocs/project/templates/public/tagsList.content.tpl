{if $element->title}
	{capture assign="moduleTitle"}
		{$element->title}
	{/capture}
{/if}
{capture assign="moduleContent"}
	{foreach from=$element->getAllTags() item=tagItem}
		{if $tagItem->amount > 0}
			<a href='{$tagItem->URL}' class="tagslist_item" style="font-size: {$tagItem->getFontSize($element->maxAmount)}em">{$tagItem->title}</a>
		{/if}
	{/foreach}
{/capture}
{assign moduleClass "tagslist_block"}
{assign moduleTitleClass ""}
{assign moduleContentClass ""}

{include file=$theme->template("component.contentmodule.tpl")}