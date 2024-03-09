{if !empty($form)}
	{$controls = $form->getAdditionalControls()}
{else}
	{$controls = true}
{/if}

{if $element->hasActualStructureInfo()}
	<div class="content_list_block">
		<form class="content_list_form" action="{$currentElement->getFormActionURL()}" method="post" enctype="multipart/form-data">
			{if $controls}
				{*				{if $currentElement->getAllowedTypes($currentElement->getActionName()) || !empty($actionButtons)}*}
				<div class='controls_block content_list_controls'>
					{if isset($formElement)}{$elementId=$formElement->id}{else}{$elementId=$rootElement->id}{/if}
					<input type="hidden" class="content_list_form_id" value="{$elementId}" name="id" />
					<input type="hidden" class="content_list_form_action" value="" name="action" />

					{include file=$theme->template('block.buttons.tpl') allowedTypes=$currentElement->getAllowedTypes($currentElement->getActionName())}

					<a class=" button tagslist_rare" href="{$currentElement->URL}filter:rare/">{translations name="tagslist.rare"}</a>
					<a class=" button tagslist_nonverified" href="{$currentElement->URL}filter:nonverified/">{translations name="tagslist.nonverified"}</a>
					<a class=" button tagslist_untranslated" href="{$currentElement->URL}filter:untranslated/">{translations name="tagslist.untranslated"}</a>
					<a class=" button tagslist_duplicates" href="{$currentElement->URL}filter:duplicates/">{translations name="tagslist.duplicates"}</a>
				</div>
				{*				{/if}*}
			{/if}
			{if !isset($formElement)}{$formElement = $rootElement}{/if}
			{assign 'formNames' $formElement->getFormNames()}
			{if !isset($contentList)}
				{assign 'contentList' $currentElement->getChildrenList()}
			{/if}
			{assign 'contentList' $currentElement->getTagsList()}
			{if $contentList}
				<table class='content_list'>
					<thead>
					<tr>
						<th class='checkbox_column'>
							<input class='groupbox checkbox_placeholder' type="checkbox" value='1' />
						</th>

						{foreach from=$currentElement->getPublicLanguages() item=language}
							<th class="name_column">
								{translations name='label.name'} ({$language->title})
							</th>
						{/foreach}
						<th class="">
						</th>
						<th class="amount_column">
							{translations name='label.amount'}
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
								<td class="checkbox_column">
									<input class='singlebox checkbox_placeholder' type="checkbox" name="{$formNames.elements}[{$contentItem->id}]" value="1" />
								</td>
								{foreach from=$contentItem->getTranslations() item=title}
									<td class='name_column{if $contentItem->verified} tagslist_verified{elseif $contentItem->detectUntranslated()} tagslist_duplicate{/if}'>
										<a href="{$contentItem->URL}">
											{stripdomspaces}
												<span class='icon icon_{$contentItem->structureType}'></span>
												<span class="content_item_title">
									{$title}
								</span>
											{/stripdomspaces}
										</a>
									</td>
								{/foreach}
								<td class='duplicate_column'>
									{if isset($contentItem->duplicateTag)}
										<a href="{$contentItem->duplicateTag->getUrl()}">{$contentItem->duplicateTag->title}</a>
									{/if}
								</td>
								<td class='amount_column'>
									{$contentItem->amount}
								</td>
								<td class='edit_column'>
									{if isset($privilege.showForm) && $privilege.showForm}
										<a href="{$contentItem->URL}id:{$contentItem->id}/action:showForm" class='icon icon_edit'></a>
									{/if}
								</td>
								<td class='type_column'>
									{translations name=$type}
								</td>
								<td>
									{$contentItem->dateModified}
								</td>
								<td>
									{if isset($privilege.delete) && $privilege.delete}
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
			{if isset($pager) && $currentElement->getChildrenList()}
				{include file=$theme->template("pager.tpl") pager=$pager}
			{/if}
		</div>
	</div>
{/if}