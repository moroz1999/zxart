{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<form action="{$element->URL}" method="post" enctype="multipart/form-data">
	<table class='form_table'>
		<tr {if $formErrors.title} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxitemslist.name'}:
			</td>
			<td colspan='2'>
				<input class='input_component' type="text" value="{$formData.title}" name="{$formNames.title}" />
			</td>
		</tr>
		<tr {if $formErrors.apiString} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxitemslist.apistring'}:
			</td>
			<td colspan='2'>
				<input class='input_component' type="text" value="{$formData.apiString}" name="{$formNames.apiString}" />
			</td>
		</tr>
		<tr {if $formErrors.searchFormParametersString} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxitemslist.searchformparametersstring'}:
			</td>
			<td colspan='2'>
				<input class='input_component' type="text" value="{$formData.searchFormParametersString}" name="{$formNames.searchFormParametersString}" />
			</td>
		</tr>
		<tr {if $formErrors.buttonTitle} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxitemslist.buttontitle'}:
			</td>
			<td colspan='2'>
				<input class='input_component' type="text" value="{$formData.buttonTitle}" name="{$formNames.buttonTitle}" />
			</td>
		</tr>
		<tr>
			<td class="form_label">
				{translations name='zxitemslist.requiresuser'}:
			</td>
			<td>
				<input class='checkbox_placeholder' type="checkbox" value="1" name="{$formNames.requiresUser}"{if $element->requiresUser} checked="checked"{/if}/>
			</td>
		</tr>
		
		<tr {if $formErrors.items} class="form_error"{/if}>
			<td class="form_label">
				{translations name='zxitemslist.items'}:
			</td>
			<td colspan='2'>
				<select class="dropdown_placeholder" name="{$formNames.items}" autocomplete='off'>
					<option value=''></option>
					<option value='graphics' {if $formData.items == 'graphics'}selected="selected"{/if}>graphics</option>
					<option value='music' {if $formData.items == 'music'}selected="selected"{/if}>music</option>
					<option value='zxProd' {if $formData.items == 'zxProd'}selected="selected"{/if}>zxProd</option>
					<option value='zxRelease' {if $formData.items == 'zxRelease'}selected="selected"{/if}>zxRelease</option>
					<option value='all' {if $formData.items == 'all'}selected="selected"{/if}>all</option>
				</select>
			</td>
		</tr>
	</table>
	{include file=$theme->template('block.controls.tpl')}
</form>
