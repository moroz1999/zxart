<div class="content_list_block">

	<form action="{$currentElement->getFormActionURL()}" method="post" enctype="multipart/form-data">
		{assign var='formNames' value=$rootElement->getFormNames()}
			{*  __ data table *}
			<table class='content_list'>
			<thead>
				<tr>
					<th class="name_column">
						{translations name='label.name'}
					</th>
					<th class='edit_column'>
						{translations name='label.edit'}
					</th>
					<th class='type_column'>
						{translations name='label.type'}
					</th>
					<th class='date_column'>
						{translations name='label.dateCreated'}
					</th>
					<th class='date_column'>
						{translations name='label.dateModified'}
					</th>
				</tr>
			</thead>
			<tbody>
			{if $currentElement->getChildrenList()}
			{foreach from=$currentElement->getChildrenList() item=contentItem}
				{if $contentItem->structureType != 'positions'}
				{assign var='typeName' value=$contentItem->structureType}
				{assign var='typeLowered' value=$contentItem->structureType|strtolower}
				{assign var='type' value="element."|cat:$typeLowered}
				{assign var='privilege' value=$privileges.$typeName}
				<tr class="content_list_item elementid_{$contentItem->id}">
					<td class='name_column'>
						<a href="{$contentItem->URL}">
							<span class='icon icon_{$contentItem->structureType}'></span>{$contentItem->getTitle()}
						</a>
					</td>
					<td class="edit_column">
						{if isset($privilege.showForm) && $privilege.showForm}
							<a href="{$contentItem->URL}id:{$contentItem->id}/action:showForm" class='icon icon_edit'></a>
						{/if}
					</td>
					<td class='type_column'>
						{translations name=$type}
					</td>
					<td class='date_column'>
						{$contentItem->dateCreated}
					</td>
					<td class='date_column'>
						{$contentItem->dateModified}
					</td>
				</tr>
				{/if}
			{/foreach}
			{else}
				<tr class="content_list_item">
					<td colspan="4">
						{translations name='label.noelements'}
					</td>
				</tr>
			{/if}
			</tbody>
			</table>

	</form>
</div>