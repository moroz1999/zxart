{if $element->title}
	{capture assign="moduleTitle"}
		{$element->title}
	{/capture}
{/if}
{capture assign="moduleContent"}
	<div>
		{if $claimApproved}
			{translations name='author.claimapproved'}: {translations name='author.claimapprove_yes'}
		{else}
			{translations name='author.claimapproved'}: {translations name='author.claimapprove_no'}
		{/if}
	</div>
	<div>
		{if $claimResultSent}
			{translations name='author.claimresultsent'}: {translations name='author.claimapprove_yes'}
		{else}
			{translations name='author.claimresultsent'}: {translations name='author.claimapprove_no'}
		{/if}
	</div>
{/capture}

{assign moduleClass "author_details"}
{assign moduleAttributes ""}
{assign moduleTitleClass ""}
{assign moduleContentClass ""}

{include file=$theme->template("component.contentmodule.tpl")}