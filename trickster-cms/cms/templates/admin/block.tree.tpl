{if $menuLevel}
	<ul class="treemenu_level treemenu_level_{$level} coount_{count($menuLevel)}">
	{foreach from=$menuLevel item=treeItem}
		<li class='treemenu_item{if $treeItem->final} treemenu_selected_item{elseif $treeItem->requested} treemenu_requested_item {$level}_{$requests_level}{/if}'>
			<a href="{$treeItem->URL}" class='structure_element' title="element {$treeItem->id}">
				<span class="icon icon_{if $treeItem->structureType == 'root' && $treeItem->marker == 'admin_root'}website{else}{$treeItem->structureType}{/if}"></span>
				<span class="treemenu_item_title">{$treeItem->getTitle()}</span>
			</a>
			{if $treeItem->requested}{$requests_level = ($requests_level +1)}
				{include file=$theme->template("block.tree.tpl") menuLevel=$treeItem->getChildrenList("container") level=$level+1 requests_level = $requests_level}
			{/if}
		</li>
	{/foreach}
	</ul>
{/if}