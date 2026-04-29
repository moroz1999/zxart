{if !isset($type)}{$type="copy"}{/if}
{if $menuLevel}
	<ul class="treemenu_level treemenu_level_{$level}">
	{foreach from=$menuLevel item=treeItem}
		<li class='treemenu_item{if $treeItem->navigated} treemenu_selected_item{/if}'>
			<a href="{$element->URL}action:{$type}Elements/id:{$element->id}/navigateId:{$treeItem->id}/{if !empty($contentType)}view:{$contentType}/{/if}" class='structure_element' title="element {$treeItem->id}">
				<span class="icon icon_{$treeItem->structureType}"></span><span class="treemenu_item_title">{$treeItem->getTitle()}</span>
			</a>
			{if count($treeItem->getChildrenList())>0 && $treeItem->navigated == true}
				{include file=$theme->template("block.copytree.tpl") menuLevel=$treeItem->getChildrenList() level=$level+1 type=$type}
			{/if}
		</li>
	{/foreach}
	</ul>
{/if}