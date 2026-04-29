{if !isset($formElement)}{$formElement = $rootElement}{/if}
	{assign 'formNames' $formElement->getFormNames()}
{if !isset($contentList)}
	{assign 'contentList' $currentElement->getChildrenList()}
{/if}
{if $contentList}
	<table class='content_list'>
		<thead>
			<tr>
				<th class='checkbox_column'>
					<input class='groupbox checkbox_placeholder' type="checkbox" value='1' />
				</th>
				{if method_exists(reset($contentList), 'getImageUrl')}
					<th class='image_column'>
						{translations name='label.image'}
					</th>
				{/if}
				<th class="name_column">
					{translations name='label.name'}
					<span class="icon icon_sort"></span>
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
				<th class='delete_column'>
					{translations name='label.delete'}
				</th>
			</tr>
		</thead>
		<tbody>
		{foreach $contentList as $contentItem}
			{assign var='typeName' value=$contentItem->structureType}
			{assign var='typeLowered' value=$contentItem->structureType|strtolower}
			{assign var='type' value="element."|cat:$typeLowered}
			{assign var='privilege' value=$privileges.$typeName}

			<tr class="content_list_item elementid_{$contentItem->id}">
				<td class="checkbox_column">
					<input class='singlebox checkbox_placeholder' type="checkbox" name="{$formNames.elements}[{$contentItem->id}]" value="1" />
				</td>
				{if method_exists ($contentItem, 'getImageUrl')}
					<td class='image_column'>
						{if $contentItem->image}
							<img src='{$contentItem->getImageUrl()}' alt=" " />
						{/if}
					</td>
				{/if}
				<td class='name_column'>
					<a class="content_element_title" href="{$contentItem->URL}">
						{stripdomspaces}
						<span class='icon icon_{$contentItem->structureType}'></span>
						<span class="content_item_title">
							{$contentItem->getTitle()}
						</span>
						{/stripdomspaces}
					</a>
				</td>
				<td class='edit_column'>
					{if isset($privilege.showForm) && $privilege.showForm}
						<a href="{$contentItem->URL}id:{$contentItem->id}/action:showForm" class='icon icon_edit'></a>
					{/if}
				</td>
				<td class='type_column'>
					{translations name=$type}
				</td>
				<td class="date_column">
					{$contentItem->dateCreated}
				</td>
				<td class="date_column">
					{$contentItem->dateModified}
				</td>
				<td class="delete_column">
					{if isset($privilege.delete) && $privilege.delete}
						<a href="{$contentItem->URL}id:{$contentItem->id}/action:delete" class='icon icon_delete content_item_delete_button'></a>
					{/if}
				</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
{/if}
