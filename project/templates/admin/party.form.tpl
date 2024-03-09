{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<form action="{$element->URL}" method="post" class="party_form" enctype="multipart/form-data">
	<table class='form_table'>
		<tr {if $formErrors.title} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.name'}:
			</td>
			<td colspan='2'>
				<input class='input_component' type="text" value="{$formData.title}" name="{$formNames.title}" />
			</td>
		</tr>
		<tr {if $formErrors.structureName} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.urlname'}:
			</td>
			<td colspan='2'>
				<input class='input_component' type="text" value="{$formData.structureName}" name="{$formNames.structureName}" />
			</td>
		</tr>

		<tr {if $formErrors.abbreviation} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.abbreviation'}:
			</td>
			<td colspan='2'>
				<input class='input_component' type="text" value="{$formData.abbreviation}" name="{$formNames.abbreviation}" />
			</td>
		</tr>


		<tr {if $formErrors.country} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.country'}:
			</td>
			<td colspan='2'>
				<select class="party_form_country_select" name="{$formNames.country}" autocomplete='off'>
					{assign var="countryElement" value=$element->getCountryElement()}
					{if $countryElement}
						<option value='{$countryElement->id}' selected="selected">
							{$countryElement->title}
						</option>
					{/if}
				</select>
			</td>
		</tr>
		<tr {if $formErrors.city} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.city'}:
			</td>
			<td colspan='2'>
				<select class="party_form_city_select" name="{$formNames.city}" autocomplete='off'>
					{assign var="cityElement" value=$element->getCityElement()}
					{if $cityElement}
						<option value='{$cityElement->id}' selected="selected">
							{$cityElement->title}
						</option>
					{/if}
				</select>
			</td>
		</tr>
	</table>
	{include file=$theme->template('block.controls.tpl')}
</form>
