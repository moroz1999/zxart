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
	</table>
	{include file=$theme->template('block.controls.tpl')}
</form>
