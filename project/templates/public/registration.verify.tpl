{capture assign="moduleTitle"}
	{$element->title}
{/capture}
{if $element->resultMessage != ''}
	{capture assign="moduleContent"}
		{$element->resultMessage}
	{/capture}
{/if}


{assign moduleClass "result_block"}
{assign moduleContentClass "form_result_message"}

{include file=$theme->template("component.contentmodule.tpl")}
