{if $h1 = $element->getH1()}
	{capture assign="moduleTitle"}
		{$h1}
	{/capture}
{/if}

{capture assign="moduleContent"}
	{include file=$theme->template("component.eventslist.tpl")}
{/capture}

{assign moduleClass "eventslist eventslist_$currentLayout"}
{assign moduleTitleClass "eventslist_title"}
{assign moduleContentClass "eventslist_content"}

{include file=$theme->template("component.contentmodule.tpl")}
