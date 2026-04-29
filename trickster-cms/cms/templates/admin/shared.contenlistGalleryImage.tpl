{if !isset($formElement)}{$formElement = $rootElement}{/if}
{assign 'formNames' $formElement->getFormNames()}
<table class='content_list gallery_form_images_table'>
	<thead>
	<tr>
		<th class='checkbox_column'>
			<input class='groupbox checkbox_placeholder' type="checkbox" value='1' />
		</th>

		<th>
			{translations name='label.image'}
		</th>

		<th class="generic">
			{translations name='label.name'}
		</th>
		<th class="generic">
			{translations name='label.alt'}
		</th>
		<th class='type_column'>
			{translations name='label.type'}
		</th>
		<th class='edit_column'>
			{translations name='label.edit'}
		</th>
		<th class='delete_column'>
			{translations name='label.delete'}
		</th>
	</tr>
	</thead>
	<tbody class="gallery_form_images_list">
	{if !empty($contentList)}
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
					<td class='gallery_form_image_imagecell'>
						{if $contentItem->image}
							<img src='{$contentItem->getImageUrl()}' alt=" " />
						{/if}
					</td>
				{/if}
				<td class='generic'>
					{$contentItem->getTitle()}
				</td>
				<td class="generic gallery_form_image_alt">
					{$contentItem->alt}
				</td>
				<td class='type_column'>
				{translations name=$type}
				</td>
				<td class='edit_column'>
					{if isset($privilege.showForm) && $privilege.showForm}
						<a href="{$contentItem->URL}id:{$contentItem->id}/action:showForm" class='icon icon_edit'></a>
					{/if}
				</td>
				<td class="delete_column">
					{if isset($privilege.delete) && $privilege.delete}
						<a href="{$contentItem->URL}id:{$contentItem->id}/action:delete" class='icon icon_delete content_item_delete_button'></a>
					{/if}
				</td>
			</tr>
		{/foreach}
	{/if}
	</tbody>
</table>
<div class="content_list_bottom">
</div>