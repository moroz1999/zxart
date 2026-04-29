{if $element->content != ""}
	{capture assign="moduleContent"}
		{$element->content}
	{/capture}
{/if}
{assign moduleClass "errorpage_block"}
{assign moduleContentClass "errorpage_content html_content"}

{include file=$theme->template("component.contentmodule.tpl")}