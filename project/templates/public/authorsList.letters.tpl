{if $element->requested}
	{capture assign="moduleContent"}
			<div class="letter_editing_controls editing_controls">
				{if isset($privileges.author.showPublicForm) && $privileges.author.showPublicForm == true}
					<a class="button button_primary" href="{$element->URL}type:author/action:showPublicForm/">{translations name='letter.add_author'}</a>
				{/if}
				{if isset($privileges.authorAlias.showPublicForm) && $privileges.authorAlias.showPublicForm == true}
					<a class="button button_primary" href="{$element->URL}type:authorAlias/action:showPublicForm/">{translations name='letter.add_authoralias'}</a>
				{/if}
			</div>
		<zx-author-browser element-id="{$element->id}" mode="full"{if $currentLetter} letter="{$currentLetter}"{/if}{if $authorsListItems} items="{$authorsListItems}"{/if}></zx-author-browser>
	{/capture}
	{assign moduleTitle ""}
	{assign moduleClass ""}
	{assign moduleTitleClass ""}
	{assign moduleContentClass ""}

	{include file=$theme->template("component.contentmodule.tpl")}
{/if}