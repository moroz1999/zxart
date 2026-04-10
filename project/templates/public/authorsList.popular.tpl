{if $element->title}
	{capture assign="moduleTitle"}
		{$element->title}
	{/capture}
{/if}
{capture assign="moduleContent"}
	{if $element->items == 'music'}
		{assign var='popularSorting' value='musicRating,desc'}
	{else}
		{assign var='popularSorting' value='graphicsRating,desc'}
	{/if}
	<zx-author-browser element-id="{$element->id}" mode="simple" sorting="{$popularSorting}" limit="50" types="author"></zx-author-browser>
{/capture}
{assign moduleClass ""}
{assign moduleTitleClass ""}
{assign moduleContentClass ""}

{include file=$theme->template("component.contentmodule.tpl")}