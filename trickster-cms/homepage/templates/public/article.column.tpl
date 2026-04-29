{capture assign="moduleTitle"}
	{$element->title}
{/capture}
{if $element->content != ""}
	{capture assign="moduleContent"}
		{$element->content}
	{/capture}
{/if}
{assign moduleClass "article_block"}
{assign moduleTitleClass "article_column_heading"}
{assign moduleContentClass "article_column_content html_content"}

{include file=$theme->template("component.columnmodule.tpl")}