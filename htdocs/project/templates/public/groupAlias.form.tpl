{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<form action="{$element->URL}" method="post" class="groupalias_form" enctype="multipart/form-data">
	<table class='form_table'>
		<tr{if $formErrors.title} class="form_error"{/if}>
			<td class="form_label">
				{translations name='groupalias.title'}:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.title}" name="{$formNames.title}" />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="title"}
			</td>
		</tr>
		<tr {if $formErrors.startDate} class="form_error"{/if}>
			<td class="form_label">
				{translations name='groupalias.startdate'}:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.startDate}" name="{$formNames.startDate}" />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="startDate"}
			</td>
		</tr>
		<tr {if $formErrors.endDate} class="form_error"{/if}>
			<td class="form_label">
				{translations name='groupalias.enddate'}:
			</td>
			<td class="form_field">
				<input class='input_component' type="text" value="{$formData.endDate}" name="{$formNames.endDate}" />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="endDate"}
			</td>
		</tr>
		<tr {if $formErrors.groupId} class="form_error"{/if}>
			<td class="form_label">
				{translations name='groupalias.group'}:
			</td>
			<td class="form_field">
				<select class="groupalias_form_group_select" name="{$formNames.groupId}" autocomplete='off'>
					{assign var="groupElement" value=$element->getGroupElement()}
					{if $groupElement}
						<option value='{$groupElement->id}' selected="selected">
							{$groupElement->title}
						</option>
					{/if}
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="groupId"}
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<h3>{translations name='group.authors'}:</h3>
			</td>
		</tr>
		{include file=$theme->template('component.form.authors.tpl') element=$element displayDate=true type='group' translationsGroup='group'}
	</table>
	{if $element->hasActualStructureInfo()}
		{include file=$theme->template('component.controls.tpl') action='publicReceive'}
	{else}
		{include file=$theme->template('component.controls.tpl') action='publicAdd'}
	{/if}
</form>
