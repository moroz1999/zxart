{if $element->title}
	{capture assign="moduleTitle"}
		{$element->title}
	{/capture}
{/if}
{capture assign="moduleContent"}
		<div class="letter_editing_controls editing_controls">
			{if isset($privileges.group.showPublicForm) && $privileges.group.showPublicForm == true}
				<a class="button button_primary" href="{$element->URL}type:group/action:showPublicForm/">{translations name='letter.add_group'}</a>
			{/if}
			{if isset($privileges.groupAlias.showPublicForm) && $privileges.groupAlias.showPublicForm == true}
				<a class="button button_primary" href="{$element->URL}type:groupAlias/action:showPublicForm/">{translations name='letter.add_groupalias'}</a>
			{/if}
		</div>
	{include file=$theme->template("component.groupstable.tpl") groupsList=$element->getGroupsList()}
{/capture}
{assign moduleClass ""}
{assign moduleTitleClass ""}
{assign moduleContentClass ""}

{include file=$theme->template("component.contentmodule.tpl")}