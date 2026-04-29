{assign var='formNames' value=$rootElement->getFormNames()}

{if $currentElement->getChildrenList()}
	<table class='content_list'>
	<tbody>
		<tr>

			<th class='checkbox_column'>

			</th>
			<th>
				{translations name='label.name'}
			</th>
		</tr>
	{foreach from=$currentElement->getChildrenList() item=contentItem}
		{if $contentItem->structureType != 'positions' && $contentItem->structureType != 'login'}
		{assign var='typeName' value=$contentItem->structureType}
		{assign var='typeLowered' value=$contentItem->structureType|strtolower}
		{assign var='type' value="element."|cat:$typeLowered}
		{assign var='privilege' value=$privileges.$typeName}
		<tr class="content_list_item elementid_{$contentItem->id}">
			<td>
				<a href="{$contentItem->URL}" class='icon {$contentItem->structureType}'></a>
			</td>
			<td class='name_column'>
				<a href="{$contentItem->URL}">
					{$contentItem->getTitle()}
				</a>
			</td>
		</tr>
		{/if}
	{/foreach}
	</tbody>
	</table>
{/if}