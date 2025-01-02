{if $element->requested}
	{capture assign="moduleContent"}
			<div class="letter_editing_controls editing_controls">
				{if isset($privileges.author.showPublicForm) && $privileges.author.showPublicForm == true}
					<a class="button" href="{$element->URL}type:author/action:showPublicForm/">{translations name='letter.add_author'}</a>
				{/if}
				{if isset($privileges.authorAlias.showPublicForm) && $privileges.authorAlias.showPublicForm == true}
					<a class="button" href="{$element->URL}type:authorAlias/action:showPublicForm/">{translations name='letter.add_authoralias'}</a>
				{/if}
			</div>
{*		{if $authorsList = $element->getLetterAuthors()}*}
{*			{include file=$theme->template("component.authorstable.tpl") authorsList=$authorsList}*}
{*		{/if}*}
	{/capture}
	{assign moduleTitle ""}
	{assign moduleClass ""}
	{assign moduleTitleClass ""}
	{assign moduleContentClass ""}

	{include file=$theme->template("component.contentmodule.tpl")}
{/if}