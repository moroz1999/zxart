{if $element->title}
	{capture assign="moduleTitle"}
		{$element->title}
	{/capture}
{/if}
{capture assign="moduleContent"}
	<zx-author-browser element-id="{$element->id}" mode="simple" sorting="id,desc" limit="300"></zx-author-browser>
{/capture}
{assign moduleClass ""}
{assign moduleTitleClass ""}
{assign moduleContentClass ""}

{include file=$theme->template("component.contentmodule.tpl")}