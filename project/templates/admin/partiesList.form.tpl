{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<form action="{$element->URL}" method="post" enctype="multipart/form-data">
	<table class='form_table'>
		<tr {if $formErrors.title} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.name'}:
			</td>
			<td colspan='2'>
				<input class='input_component' type="text" value="{$formData.title}" name="{$formNames.title}" />
			</td>
		</tr>
		<tr {if $formErrors.type} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.type'}:
			</td>
			<td colspan='2'>
				<select class="dropdown_placeholder" name="{$formNames.type}" autocomplete='off'>
					<option value=''></option>
					<option value='latest' {if $formData.type == 'latest'}selected="selected"{/if}>latest</option>
					<option value='recent' {if $formData.type == 'recent'}selected="selected"{/if}>recent</option>
				</select>
			</td>
		</tr>
	</table>
	{include file=$theme->template('block.controls.tpl')}
</form>
