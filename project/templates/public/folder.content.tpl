{assign var='_authorsContent' value=$element->getContentElements()}
{assign var='_authorsAnchor' value=null}
{foreach $_authorsContent as $_authorsChild}
	{if $_authorsChild->structureType == 'authorsList' && $_authorsChild->type == 'letters'}{assign var='_authorsAnchor' value=$_authorsChild}{/if}
{/foreach}
{if $_authorsAnchor}
	{if $_authorsAnchor->items == 'music'}{assign var='_authorsItems' value='music'}{else}{assign var='_authorsItems' value='graphics'}{/if}
	<zx-authors-page element-id="{$_authorsAnchor->id}" items="{$_authorsItems}"></zx-authors-page>
{else}
	{foreach $_authorsContent as $_authorsChild}{include file=$theme->template($_authorsChild->getTemplate()) element=$_authorsChild}{/foreach}
{/if}
