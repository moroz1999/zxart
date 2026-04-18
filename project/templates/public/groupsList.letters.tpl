{if $element->requested}
	{capture assign="moduleContent"}
			<div class="letter_editing_controls editing_controls">
				{if isset($privileges.group.showPublicForm) && $privileges.group.showPublicForm == true}
					<a class="button button_primary" href="{$element->URL}type:group/action:showPublicForm/">{translations name='letter.add_group'}</a>
				{/if}
				{if isset($privileges.groupAlias.showPublicForm) && $privileges.groupAlias.showPublicForm == true}
					<a class="button button_primary" href="{$element->URL}type:groupAlias/action:showPublicForm/">{translations name='letter.add_groupalias'}</a>
				{/if}
			</div>
		<zx-group-browser element-id="{$element->id}" mode="full"{if $currentLetter} letter="{$currentLetter}"{/if}></zx-group-browser>
	{/capture}
	{assign moduleTitle ""}
	{assign moduleClass ""}
	{assign moduleTitleClass ""}
	{assign moduleContentClass ""}

	{include file=$theme->template("component.contentmodule.tpl")}
{/if}