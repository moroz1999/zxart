{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<form action="{$element->URL}" method="post" enctype="multipart/form-data" class="jointag_form">
	<table class='form_table'>
		<tr {if $formErrors.structureName} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.name'}:
			</td>
			<td>
				<input class='input_component' type="text" value="{$formData.structureName}" name="{$formNames.structureName}" />
			</td>
		</tr>
		<tr {if $formErrors.structureName} class="form_error"{/if}>
			<td class="form_label">
				{translations name='tag.amount'}:
			</td>
			<td>
				<input class='input_component' type="text" value="{$element->amount}" name="" disabled/>
			</td>
		</tr>
		{foreach from=$formData.title key=languageId item=title}
		<tr {if $formErrors.title.$languageId} class="form_error"{/if}>
			<td class="form_label">
				{translations name='tag.title'} ({$languageNames.$languageId})
			</td>
			<td>
				<input class='input_component' type="text" value="{$title}" name="{$formNames.title.$languageId}" />
			</td>
		</tr>
		{/foreach}
		{foreach from=$formData.synonym key=languageId item=synonym}
		<tr {if $formErrors.synonym.$languageId} class="form_error"{/if}>
			<td class="form_label">
				{translations name='tag.synonyms'} ({$languageNames.$languageId})
			</td>
			<td>
				<input class='input_component' type="text" value="{$synonym}" name="{$formNames.synonym.$languageId}" />
			</td>
		</tr>
		{/foreach}
		{foreach from=$formData.description key=languageId item=description}
		<tr {if $formErrors.description.$languageId} class="form_error"{/if}>
			<td class="form_label">
				{translations name='tag.description'} ({$languageNames.$languageId})
			</td>
			<td>
				<input class='input_component' type="text" value="{$description}" name="{$formNames.description.$languageId}" />
			</td>
		</tr>
		{/foreach}
		<tr class="picturetags_form{if $formErrors.joinTag} form_error{/if}">
			<td class="form_label">
				{translations name='tag.jointags'}:
			</td>
			<td>
				<input autocomplete="off" type="text" class="input_component jointag_input" data-id="{$element->id}" value="" />
				<input autocomplete="off" type="hidden" class="jointag_value" value="" name="{$formNames.joinTag}" />
			</td>
		</tr>
		<tr>
			<td class="form_label">
				{translations name='tag.verified'}:
			</td>
			<td>
				<input class='checkbox_placeholder' type="checkbox" value="1" name="{$formNames.verified}"{if $element->verified} checked="checked"{/if}/>
			</td>
		</tr>
	</table>
	{include file=$theme->template('block.controls.tpl')}
</form>
