<div class="feedback_details">
	{assign var='formData' value=$element->getFormData()}
	{assign var='formErrors' value=$element->getFormErrors()}
	{assign var='formNames' value=$element->getFormNames()}
	{$answersTable = $element->getAnswersTable()}

	{if $element->hasActualStructureInfo()}
		<div class="content_list_block">
			<form class="content_list_form" action="{$currentElement->getFormActionURL()}" method="post" enctype="multipart/form-data">
				<div class='controls_block content_list_controls'>
					<input type="hidden" value="{$rootElement->id}" name="id" />
					<input type="hidden" class="content_list_form_action" value="deleteElements" name="action" />
					{if isset($rootPrivileges.deleteElements)}
						<button class='actions_form_button button warning_button actions_form_delete'><span class="icon icon_delete"></span>{translations name='button.deleteselected'}</button>
					{/if}
					<a class="actions_form_button button primary_button" href="{$element->URL}id:{$element->id}/action:export/">{translations name='feedback.csv_export'}</a>
				</div>

				{assign 'formNames' $rootElement->getFormNames()}
				<div class="feedback_content_list_wrapper">
					<table class='content_list'>
						<thead>
							<tr>
								<th class='checkbox_column'>
									<input class='groupbox checkbox_placeholder' type="checkbox" value='1' />
								</th>
								{foreach $answersTable.header as $cellName}
									<th class="name_column">
										{$cellName}
									</th>
								{/foreach}
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
						{foreach $answersTable.answers as $answer}
							{assign var='typeName' value=$answer.element->structureType}
							{assign var='typeLowered' value=$answer.element->structureType|strtolower}
							{assign var='type' value="element."|cat:$typeLowered}
							{assign var='privilege' value=$privileges.$typeName}
							<tr class="content_list_item elementid_{$answer.element->id}">
								<td class="checkbox_cell">
									<input class='singlebox checkbox_placeholder' type="checkbox" name="{$formNames.elements}[{$answer.element->id}]" value="1" />
								</td>
								{foreach $answer.fields as $fieldInfo}
									<td class="name_column {$fieldInfo.type}_column">
										{if $fieldInfo.type != 'fileinput'}
                                            {if $fieldInfo.value|is_array}
                                                {foreach $fieldInfo.value as $value}
                                                    {if $value.checked == true}
                                                        <div class="form_value_multiple"><a href="{$answer.element->URL}">{$value.name}</a></div>
                                                    {/if}
                                                {/foreach}
                                            {else}
                                                <a href="{$answer.element->URL}">{$fieldInfo.value}</a>
                                            {/if}
										{else}
											{if !empty($fieldInfo.files)}
												{foreach $fieldInfo.files as $file}
													<a target="_blank" href="{$file.link}">{$file.originalName}</a>
												{/foreach}
											{/if}
										{/if}
									</td>
								{/foreach}
								<td class='date_column'>
									{$answer.element->dateCreated}
								</td>
								<td class='date_column'>
									{$answer.element->dateModified}
								</td>
								<td class='delete_column'>
									{if isset($privilege.delete) && $privilege.delete}
										<a href="{$answer.element->URL}id:{$answer.element->id}/action:delete" class='icon icon_delete content_item_delete_button'></a>
									{/if}
								</td>
							</tr>
						{/foreach}
						</tbody>
					</table>
				</div>
			</form>

		</div>
	{/if}
</div>