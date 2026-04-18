{if $element->title}
	{capture assign="moduleTitle"}
		{$element->title}
	{/capture}
{/if}
{capture assign="moduleContent"}
	<zx-group-browser element-id="{$element->id}" mode="simple" sorting="id,desc" limit="300"></zx-group-browser>
{/capture}
{assign moduleClass ""}
{assign moduleTitleClass ""}
{assign moduleContentClass ""}

{include file=$theme->template("component.contentmodule.tpl")}