{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<form action="{$element->URL}" method="post" enctype="multipart/form-data">
	<table class='form_table'>
		<tr{if $formErrors.title} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.name'}:
			</td>
			<td>
				<input class='input_component' type="text" value="{$formData.title}" name="{$formNames.title}" />
			</td>
		</tr>
		<tr {if $formErrors.year} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.year'}:
			</td>
			<td>
				<input class='input_component' type="text" value="{$formData.year}" name="{$formNames.year}" />
			</td>
		</tr>
		<tr {if $formErrors.company} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.company'}:
			</td>
			<td>
				<input class='input_component' type="text" value="{$formData.company}" name="{$formNames.company}" />
			</td>
		</tr>
		<tr {if $formErrors.wosURL} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.wos'}:
			</td>
			<td>
				<input class='input_component' type="text" value="{$formData.wosURL}" name="{$formNames.wosURL}" />
			</td>
		</tr>
	</table>
	<input type="submit" value='{translations name='button.save'}'/>
	<input type="hidden" value="{$element->id}" name="id" />
	<input type="hidden" value="receive" name="action" />
</form>
