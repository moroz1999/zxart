{assign 'pager' $currentElement->getPager()}
{assign 'contentList' $currentElement->getContentList()}
<div class="content_list_block">
	<form class="content_list_form" action="{$currentElement->getFormActionURL()}" method="post" enctype="multipart/form-data">

		{if $currentElement->getAllowedTypes()}
			<div class='controls_block content_list_controls'>
				<input type="hidden" class="content_list_form_id" value="{$rootElement->id}" name="id" />
				<input type="hidden" class="content_list_form_action" value="deleteElements" name="action" />

				{include file=$theme->template('block.buttons.tpl') allowedTypes=$currentElement->getAllowedTypes()}
			</div>
		{/if}

		{assign 'formNames' $rootElement->getFormNames()}

		{if $contentList}
			<table class='content_list'>
				<thead>
					<tr>
						<th class='checkbox_column'>
							<input class='groupbox checkbox_placeholder' type="checkbox" value='1' />
						</th>
						<th class='image_column'>
							{translations name='label.image'}
						</th>
						<th class="name_column">
							{translations name='label.name'}
						</th>
						<th>
							{translations name='event.startdate'}
						</th>
						<th class='edit_column'>
							{translations name='label.edit'}
						</th>
						<th class='type_column'>
							{translations name='label.type'}
						</th>
						<th class='date_column'>
							{translations name='label.date'}
						</th>
						<th class='delete_column'>
							{translations name='label.delete'}
						</th>
					</tr>
				</thead>
				<tbody>
				{foreach $contentList as $contentItem}
					{if $contentItem->structureType != 'positions'}
					{assign var='typeName' value=$contentItem->structureType}
					{assign var='typeLowered' value=$contentItem->structureType|strtolower}
					{assign var='type' value="element."|cat:$typeLowered}
					{assign var='privilege' value=$privileges.$typeName}
					<tr class="content_list_item elementid_{$contentItem->id}">
						<td class="checkbox_cell">
							<input class='singlebox checkbox_placeholder' type="checkbox" name="{$formNames.elements}[{$contentItem->id}]" value="1" />
						</td>
						<td class='image_column'>
							{if $contentItem->image}
								<img src='{$contentItem->getImageUrl()}' alt=" " />
							{/if}
						</td>
						<td class='name_column'>
							<a href="{$contentItem->URL}">
								{stripdomspaces}
								<span class='icon icon_{$contentItem->structureType}'></span>
								<span class="content_item_title">
									{$contentItem->getTitle()}
								</span>
								{/stripdomspaces}
							</a>
						</td>
						<td>
							{$contentItem->startDate}
						</td>
						<td class='edit_column'>
							{if $privilege.showForm}
								<a href="{$contentItem->URL}id:{$contentItem->id}/action:showForm" class='icon icon_edit'></a>
							{/if}
						</td>
						<td class='type_column'>
							{translations name=$type}
						</td>
						<td class='date_column'>
							{$contentItem->dateModified}
						</td>
						<td>
							{if $privilege.delete}
								<a href="{$contentItem->URL}id:{$contentItem->id}/action:delete" class='icon icon_delete content_item_delete_button'></a>
							{/if}
						</td>
					</tr>
					{/if}
				{/foreach}
				</tbody>
			</table>
		{/if}
	</form>
	<div class="content_list_bottom">
		{if isset($pager)}
			{include file=$theme->template("pager.tpl") pager=$pager}
		{/if}
	</div>
</div>