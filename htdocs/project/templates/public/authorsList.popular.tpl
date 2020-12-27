{if $element->title}
	{capture assign="moduleTitle"}
		{$element->title}
	{/capture}
{/if}
{capture assign="moduleContent"}
	{if $authorsList = $element->getTopAuthors(50)}
		{include file=$theme->template("component.authorstable.tpl") authorsList=$authorsList}
	{/if}
{/capture}
{assign moduleClass ""}
{assign moduleTitleClass ""}
{assign moduleContentClass ""}

{include file=$theme->template("component.contentmodule.tpl")}