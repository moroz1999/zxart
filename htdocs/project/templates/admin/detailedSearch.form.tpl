{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<form action="{$element->URL}" method="post" enctype="multipart/form-data">
	<table class='form_table'>
		<tr {if $formErrors.title}class="form_error"{/if}>
			<td>
				{translations name='field.heading'}:
			</td>
			<td>
				<input class='input_component' type="text" value="{$formData.title}" name="{$formNames.title}" />
			</td>
		</tr>
		<tr {if $formErrors.items} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.items'}:
			</td>
			<td colspan='2'>
				<select class="dropdown_placeholder" name="{$formNames.items}" autocomplete='off'>
					<option value=''></option>
					<option value='graphics' {if $formData.items == 'graphics'}selected="selected"{/if}>graphics</option>
					<option value='music' {if $formData.items == 'music'}selected="selected"{/if}>music</option>
					<option value='all' {if $formData.items == 'all'}selected="selected"{/if}>all</option>
				</select>
			</td>
		</tr>
	</table>
	{include file=$theme->template('block.controls.tpl')}
	<input type="hidden" value="{$element->id}" name="id" />
	<input type="hidden" value="receive" name="action" />
</form>