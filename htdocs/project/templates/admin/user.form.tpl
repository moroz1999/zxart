{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<form action="{$element->getFormActionURL()}" class="form_component zxitem_form" method="post" enctype="multipart/form-data">
	{if $element->resultMessage != ''}
		<div class='form_result_message'>
			{$element->resultMessage}
		</div>
	{/if}
	{if $element->errorMessage != ''}
		<div class='form_error_message'>
			{$element->errorMessage}
		</div>
	{/if}
	<table class="form_table">
		<tr{if $formErrors.userName} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.username'}:
			</td>
			<td>
				<input class="input_component" type="text" value="{$formData.userName}" name="{$formNames.userName}" autocomplete='off' />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="userName"}
			</td>
		</tr>
		<tr{if $formErrors.password} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.password'}:
			</td>
			<td>
				<input class="input_component" type="password" value="" name="{$formNames.password}" autocomplete='off' />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="password"}
			</td>
		</tr>
		<tr{if $formErrors.company} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.company'}:
			</td>
			<td>
				<input class="input_component" type="text" value="{$formData.company}" name="{$formNames.company}" autocomplete='off' />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="company"}
			</td>
		</tr>
		<tr{if $formErrors.firstName} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.firstname'}:
			</td>
			<td>
				<input class="input_component" type="text" value="{$formData.firstName}" name="{$formNames.firstName}" autocomplete='off' />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="firstName"}
			</td>
		</tr>
		<tr{if $formErrors.lastName} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.lastname'}:
			</td>
			<td>
				<input class="input_component" type="text" value="{$formData.lastName}" name="{$formNames.lastName}" autocomplete='off' />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="lastName"}
			</td>
		</tr>
		<tr{if $formErrors.address} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.address'}:
			</td>
			<td>
				<input class="input_component" type="text" value="{$formData.address}" name="{$formNames.address}" autocomplete='off' />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="address"}
			</td>
		</tr>
		<tr{if $formErrors.city} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.city'}:
			</td>
			<td>
				<input class="input_component" type="text" value="{$formData.city}" name="{$formNames.city}" autocomplete='off' />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="city"}
			</td>
		</tr>
		<tr{if $formErrors.postIndex} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.postindex'}:
			</td>
			<td>
				<input class="input_component" type="text" value="{$formData.postIndex}" name="{$formNames.postIndex}" autocomplete='off' />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="postIndex"}
			</td>
		</tr>
		<tr{if $formErrors.country} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.country'}:
			</td>
			<td>
				<input class="input_component" type="text" value="{$formData.country}" name="{$formNames.country}" autocomplete='off' />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="country"}
			</td>
		</tr>
		<tr{if $formErrors.email} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.email'}:
			</td>
			<td>
				<input class="input_component" type="text" value="{$formData.email}" name="{$formNames.email}" autocomplete='off' />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="email"}
			</td>
		</tr>
		<tr{if $formErrors.phone} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.phone'}:
			</td>
			<td>
				<input class="input_component" type="text" value="{$formData.phone}" name="{$formNames.phone}" autocomplete='off' />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="phone"}
			</td>
		</tr>
		<tr{if $formErrors.website} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.website'}:
			</td>
			<td>
				<input class="input_component" type="text" value="{$formData.website}" name="{$formNames.website}" autocomplete='off' />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="website"}
			</td>
		</tr>
		<tr>
			<td class="form_label">
				{translations name='field.subscribed'}:
			</td>
			<td>
				<input class="checkbox_placeholder" type="checkbox" value="1" name="{$formNames.subscribe}" {if $formData.subscribe == '1'}checked="checked"{/if} />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="subscribe"}
			</td>
		</tr>
		<tr>
			<td class="form_label">
				{translations name='label.usergroups'}:
			</td>
			<td>
				<select class="select_multiple" multiple='multiple' name='{$formNames.userGroups}[]' id='userGroups[]' autocomplete='off'>
					<option value="">{translations name='label.notselected'}</option>
					{foreach from=$element->userGroupsList item=group}
						<option value='{$group->id}' {if $group->linkExists == true}selected="selected"{/if}>
							{$group->groupName}
						</option>
					{/foreach}
				</select>
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="userGroups"}
			</td>
		</tr>
		<tr {if $formErrors.newAuthorId} class="form_error"{/if}>
			<td class="form_label">
				{translations name='user.author'}:
			</td>
			<td colspan='2'>
				<select class="select_multiple zxitem_form_authors_select" name="{$formNames.newAuthorId}" autocomplete='off'>
					<option value=''></option>
					{foreach from=$element->getAuthorsList() item=author}
						<option value='{$author->id}' selected="selected">
							{$author->title}
						</option>
					{/foreach}
				</select>
			</td>
		</tr>
		{foreach $element->getAdditionalDataFields() as $fieldElement}
			<tr>
				<td class="form_label">
					{$fieldElement->title}:
				</td>
				<td>
					{$fieldElement->value}
				</td>
			</tr>
		{/foreach}
	</table>
	{include file=$theme->template('component.controls.tpl')}
</form>
