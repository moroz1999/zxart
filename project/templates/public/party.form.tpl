{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<form action="{$element->URL}" method="post" class="party_form" enctype="multipart/form-data">
	<table class='form_table'>
		<tr {if $formErrors.title} class="form_error"{/if}>
			<td class="form_label">
				{translations name='party.name'}:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.title}" name="{$formNames.title}" />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="title"}
			</td>
		</tr>
		<tr {if $formErrors.abbreviation} class="form_error"{/if}>
			<td class="form_label">
				{translations name='party.abbreviation'}:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.abbreviation}" name="{$formNames.abbreviation}" />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="abbreviation"}
			</td>
		</tr>
		<tr {if $formErrors.country} class="form_error"{/if}>
			<td class="form_label">
				{translations name='party.country'}:
			</td>
			<td class="form_field">
				<select class="party_form_country_select" name="{$formNames.country}" autocomplete='off'>
					{assign var="countryElement" value=$element->getCountryElement()}
					{if $countryElement}
						<option value='{$countryElement->id}' selected="selected">
							{$countryElement->title}
						</option>
					{/if}
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="country"}
			</td>
		</tr>
		<tr {if $formErrors.city} class="form_error"{/if}>
			<td class="form_label">
				{translations name='party.city'}:
			</td>
			<td class="form_field">
				<select class="party_form_city_select" name="{$formNames.city}" autocomplete='off'>
					{assign var="cityElement" value=$element->getCityElement()}
					{if $cityElement}
						<option value='{$cityElement->id}' selected="selected">
							{$cityElement->title}
						</option>
					{/if}
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="city"}
			</td>
		</tr>
		<tr {if $formErrors.image} class="form_error"{/if}>
			<td class="form_label">
				{translations name='party.image'}:
			</td>
			<td class="form_field">
				{if $element->originalName != ""}
					<img loading="lazy" src='{$controller->baseURL}image/type:adminImage/id:{$element->image}/filename:{$element->originalName}' />
					<br />
					<a href="{$element->URL}id:{$element->id}/action:deleteFile/file:image/" >{translations name='label.deleteimage'}</a>
				{else}
					<input class="fileinput_placeholder" type="file" name="{$formNames.image}" />
					{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="image"}
				{/if}
			</td>
		</tr>
	</table>
	{if $element->hasActualStructureInfo()}
		{include file=$theme->template('component.controls.tpl') action='publicReceive'}
	{else}
		{include file=$theme->template('component.controls.tpl') action='publicAdd'}
	{/if}
</form>
