<div class="content_list_block">

	<form action="{$currentElement->getFormActionURL()}" method="post" enctype="multipart/form-data">
		{assign var='formNames' value=$rootElement->getFormNames()}
		{if $currentElement->getChildrenList()}
			<table class='content_list'>
			<thead>
				<tr>
					<th class="name_column">
						{translations name='label.name'}
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
			</tbody>
			</table>

		{/if}
	</form>
</div>