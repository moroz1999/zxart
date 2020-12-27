{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<form action="{$element->URL}" method="post" class="group_form" enctype="multipart/form-data">
	<table class='form_table'>
		<tr{if $formErrors.title} class="form_error"{/if}>
			<td class="form_label">
				{translations name='group.title'}:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.title}" name="{$formNames.title}" />
			</td>
		</tr>
		<tr {if $formErrors.country} class="form_error"{/if}>
			<td class="form_label">
				{translations name='group.country'}:
			</td>
			<td class="form_field">
				<select class="group_form_country_select" name="{$formNames.country}" autocomplete='off'>
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
				{translations name='group.city'}:
			</td>
			<td class="form_field">
				<select class="group_form_city_select" name="{$formNames.city}" autocomplete='off'>
					{assign var="cityElement" value=$element->getCityElement()}
					{if $cityElement}
						<option value='{$cityElement->id}' selected="selected">
							{$cityElement->title}
						</option>
					{/if}
				</select>
			</td>
		</tr>
		<tr {if $formErrors.image} class="form_error"{/if}>
			<td class="form_label">
				{translations name='group.image'}:
			</td>
			<td class="form_field">
				{if $element->originalName != ""}
					<img src='{$controller->baseURL}image/type:adminImage/id:{$element->image}/filename:{$element->originalName}' />
					<br />
					<a href="{$element->URL}id:{$element->id}/action:deleteFile/file:image/">{translations name='label.deleteimage'}</a>
				{else}
					<input class="fileinput_placeholder" type="file" name="{$formNames.image}" />
				{/if}
			</td>
		</tr>
		<tr {if $formErrors.wikiLink} class="form_error"{/if}>
			<td class="form_label">
				{translations name='group.wiki'}:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.wikiLink}" name="{$formNames.wikiLink}" />
			</td>
		</tr>
		<tr{if $formErrors.website} class="form_error"{/if}>
			<td class="form_label">
				{translations name='group.website'}:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.website}" name="{$formNames.website}" />
			</td>
		</tr>
		<tr{if $formErrors.abbreviation} class="form_error"{/if}>
			<td class="form_label">
				{translations name='group.abbreviation'}:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.abbreviation}" name="{$formNames.abbreviation}" />
			</td>
		</tr>
		<tr{if $formErrors.startDate} class="form_error"{/if}>
			<td class="form_label">
				{translations name='group.startdate'}:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.startDate}" name="{$formNames.startDate}" onfocus="displayCalendar(this,'dd.mm.yyyy',this);" />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="startDate"}
			</td>
		</tr>
		<tr{if $formErrors.endDate} class="form_error"{/if}>
			<td class="form_label">
				{translations name='group.enddate'}:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.endDate}" name="{$formNames.endDate}" onfocus="displayCalendar(this,'dd.mm.yyyy',this);" />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="endDate"}
			</td>
		</tr>
		<tr{if $formErrors.slogan} class="form_error"{/if}>
			<td class="form_label">
				{translations name='group.slogan'}:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.slogan}" name="{$formNames.slogan}" />
			</td>
		</tr>
		{assign var='subGroups' value=$element->getSubGroups()}
		<tr>
			<td class="form_label">
				{translations name='group.subgroups'}:
			</td>
			<td class="form_field">
				<select class="select_multiple group_form_subgroup_select" multiple='multiple' name="{$formNames.subGroupsSelector}[]" autocomplete='off'>
					<option value=''></option>
					{foreach from=$subGroups item=subgroup}
						<option value="{$subgroup->id}" selected="selected">
							{$subgroup->getHumanReadableName()}
						</option>
					{/foreach}
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="subGroupsSelector"}
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<h3>{translations name='group.authors'}:</h3>
			</td>
		</tr>
		{include file=$theme->template('component.form.authors.tpl') element=$element displayDate=true type='group' translationsGroup=$element->structureType}
	</table>
	{if $element->hasActualStructureInfo()}
		{include file=$theme->template('component.controls.tpl') action='publicReceive'}
	{else}
		{include file=$theme->template('component.controls.tpl') action='publicAdd'}
	{/if}
</form>