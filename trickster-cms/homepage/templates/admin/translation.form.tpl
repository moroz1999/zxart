{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<form action="{$element->getFormActionURL()}" class="translation_form form_component" method="post" enctype="multipart/form-data">
	<table class="form_table">
		<tr{if $formErrors.structureName} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.name'}:
			</td>
			<td>
				<input class='input_component' type="text" value="{$formData.structureName}" name="{$formNames.structureName}" />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="structureName"}
			</td>
		</tr>
		<tr>
			<td class="form_label">
				{translations name='translation.type'}:
			</td>
			<td>
				<select class="dropdown_placeholder translation_form_type" name="{$formNames.valueType}">
					<option value="text"{if $formData.valueType=='text'} selected='selected'{/if}>
						{translations name='translation.type_text'}
					</option>
					<option value="textarea"{if $formData.valueType=='textarea'} selected='selected'{/if}>
						{translations name='translation.type_textarea'}
					</option>
					<option value="html"{if $formData.valueType=='html'} selected='selected'{/if}>
						{translations name='translation.type_html'}
					</option>
				</select>
			</td>
		</tr>
		<tr>
			<td class="form_label" colspan='2'>
				<h1 class="form_inner_title">{translations name='field.translation'}:</h1>
			</td>
		</tr>

		{foreach from=$formData.valueText key=languageId item=valueText}
			<tr class="translation_form_text_related" style="display: none">
				<td class="form_label">
					{$languageNames.$languageId}
				</td>
				<td>
					<input class='input_component' type="text" value="{$valueText}" name="{$formNames.valueText.$languageId}" />
					{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="translation"}
				</td>
			</tr>
		{/foreach}

		{foreach from=$formData.valueTextarea key=languageId item=valueTextarea}
			<tr class="translation_form_textarea_related" style="display: none">
				<td class="form_label">
				  {$languageNames.$languageId}:
				</td>
				<td>
				  <textarea class="textarea_component" type="text" name="{$formNames.valueTextarea.$languageId}" >{$valueTextarea}</textarea>
					{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="translation"}
				</td>
			</tr>
		{/foreach}

		{foreach from=$formData.valueHtml key=languageId item=valueHtml}
			<tr class="translation_form_html_related" style="display: none">
				<td class="form_label">
					{$languageNames.$languageId}:
				</td>
				<td>
					{include file=$theme->template('component.htmleditor.tpl') data=$valueHtml name=$formNames.valueHtml.$languageId}
					{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="translation"}
				</td>
			</tr>
		{/foreach}
	</table>
	{include file=$theme->template('component.controls.tpl')}
</form>
