{if $element->title}
	{capture assign="moduleTitle"}
		{$element->title}
	{/capture}
{/if}
{capture assign="moduleContent"}
	{if $claimRequestResult}
		{translations name='author.claimrequest_sent'}
	{else}
		{translations name='author.claimrequest_error'}
	{/if}
{/capture}

{assign moduleClass "author_details"}
{assign moduleTitleClass ""}
{assign moduleContentClass ""}

{include file=$theme->template("component.contentmodule.tpl")}