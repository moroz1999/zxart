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
	</table>
	{include file=$theme->template('block.controls.tpl')}
	<input type="hidden" value="{$element->id}" name="id" />
	<input type="hidden" value="receive" name="action" />
</form>