{if $element->title}
	{capture assign="moduleTitle"}
		{$element->title}
	{/capture}
{/if}
{capture assign="moduleContent"}
		<div class="letter_editing_controls editing_controls">
			{if isset($privileges.game.showPublicForm) && $privileges.game.showPublicForm == true}
			<a class="button" href="{$element->URL}type:game/action:showPublicForm/">{translations name='letter.add_game'}</a>
			{/if}
		</div>
	{foreach from=$currentElement->getGamesList() item=game}
		{include file=$theme->template('game.short.tpl') element=$game}
	{/foreach}
{/capture}
{assign moduleClass "games_list gallery_pictures"}
{assign moduleAttributes "id='gallery_{$currentElement->id}'"}
{assign moduleTitleClass ""}
{assign moduleContentClass ""}

{include file=$theme->template("component.contentmodule.tpl")}