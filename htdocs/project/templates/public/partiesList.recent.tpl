{if $element->title}
	{capture assign="moduleTitle"}
		{$element->title}
	{/capture}
{/if}
{capture assign="moduleContent"}
	{if $parties = $element->getRecentParties()}
		<div class="partieslist_parties">
			{foreach $parties as $party}
				{include file=$theme->template('party.short.tpl') element=$party}
			{/foreach}
		</div>
	{/if}
{/capture}
{assign moduleClass ""}
{assign moduleTitleClass ""}
{assign moduleContentClass ""}

{include file=$theme->template("component.contentmodule.tpl")}