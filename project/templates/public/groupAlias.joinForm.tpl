{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<form action="{$element->URL}" method="post" class="group_form" enctype="multipart/form-data">
	<table class='form_table'>
		<tr class="{if $formErrors.joinAndDelete} form_error{/if}">
			<td class="form_label">
				{translations name='groupalias.joinanddelete'}:
			</td>
			<td class="form_field">
				<select class="group_form_joinanddelete_select" name="{$formNames.joinAndDelete}" autocomplete='off'></select>
			</td>
		</tr>
	</table>
	{include file=$theme->template('component.controls.tpl') action='join'}
</form>
