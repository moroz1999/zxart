{if $element->title}
	{capture assign="moduleTitle"}
		{$element->title}
	{/capture}
{/if}
{capture assign="moduleContent"}
		<div class="year_editing_controls editing_controls">
			{if isset($privileges.party.showPublicForm) && $privileges.party.showPublicForm == true}
				<a class="button" href="{$element->URL}type:party/action:showPublicForm/">{translations name='year.add_party'}</a>
			{/if}
		</div>
	{include file=$theme->template("component.partiestable.tpl") partiesList=$currentElement->getPartiesList()}
{/capture}
{assign moduleClass ""}
{assign moduleTitleClass ""}
{assign moduleContentClass ""}

{include file=$theme->template("component.contentmodule.tpl")}