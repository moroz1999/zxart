<div class="content_list_block">

	<form action="{$currentElement->getFormActionURL()}" method="post" enctype="multipart/form-data">

	{assign var='formNames' value=$rootElement->getFormNames()}
	{assign "incompleteTranslations" $currentElement->getIncompleteTranslations()}
	{if $incompleteTranslations}
		<table class='content_list'>
			<thead>
				<tr>
					<th class="name_column">
						{translations name='label.group'}
					</th>
					<th class="name_column">
						{translations name='label.name'}
					</th>
					<th>
						{translations name='translations.missinglanguages'}
					</th>
					<th class='date_column'>
						{translations name='label.date'}
					</th>
				</tr>
			</thead>
			<tbody>
			{foreach from=$incompleteTranslations item=translation}
				{if $translation->structureType != 'positions'}
				{assign var='typeName' value=$translation->structureType}
				{assign var='typeLowered' value=$translation->structureType|strtolower}
				{assign var='type' value="element."|cat:$typeLowered}
				{assign var='privilege' value=$privileges.$typeName}
				<tr class="content_list_item elementid_{$translation->id}">
					<td class='name_column'>
						<span class='icon icon_{$translation->group->structureType}'></span>
						<a href="{$translation->group->URL}">{$translation->group->title}</a>
					</td>
					<td class='name_column'>
						<a href="{$translation->URL}">
							{stripdomspaces}
							<span class='icon icon_{$translation->structureType}'></span>
							<span class="content_item_title">
								{$translation->getTitle()}
							</span>
							{/stripdomspaces}
						</a>
					</td>
					<td>
						{', '|implode:$translation->missingLanguages}
					</td>
					<td>
						{$translation->dateModified}
					</td>
				</tr>
				{/if}
			{/foreach}
			</tbody>
		</table>
	{/if}
	</form>

</div>