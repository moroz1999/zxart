{if $currentUser->userName != 'anonymous' || $element->requiresUser == 0}
	{if $element->title}
		{capture assign="moduleTitle"}
			{$element->title}
		{/capture}
	{/if}
	{capture assign="moduleContent"}
		{include file=$theme->template("component.musictable.tpl") musicList=$element->getItemsList() element=$element}
		{if $musicDetailedSearchElement && $element->searchFormParametersString}
			<div class="zxitemslist_controls">
				<a class="zxitemslist_link button" href="{$musicDetailedSearchElement->URL}{$element->searchFormParametersString}">{translations name='zxitemslist.seemoremusic'}</a>
			</div>
		{/if}
	{/capture}
	{assign moduleClass "zxitemslist zxitemslist_music"}
	{assign moduleTitleClass ""}
	{assign moduleContentClass ""}

	{include file=$theme->template("component.contentmodule.tpl")}
{/if}