{if $currentUser->userName != 'anonymous' || $element->requiresUser == 0}
	{if $element->title}
		{capture assign="moduleTitle"}
			{$element->title}
		{/capture}
	{/if}
	{capture assign="moduleContent"}
		{stripdomspaces}
		{if $itemsList = $element->getItemsList()}
			{include file=$theme->template('component.pictureslist.tpl') pictures=$itemsList}
		{/if}
		{/stripdomspaces}
		{if $picturesDetailedSearchElement && $element->searchFormParametersString}
			<div class="zxitemslist_controls">
				<a class="zxitemslist_link button" href="{$picturesDetailedSearchElement->URL}{$element->searchFormParametersString}">{translations name='zxitemslist.seemoregraphics'}</a>
			</div>
		{/if}
	{/capture}
	{assign moduleClass "zxitemslist zxitemslist_graphics gallery_pictures"}
	{assign moduleAttributes "id='gallery_{$element->id}'"}
	{assign moduleTitleClass ""}
	{assign moduleContentClass ""}

	{include file=$theme->template("component.contentmodule.tpl")}
{/if}