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
		<tr{if $formErrors.email} class="form_error"{/if}>
			<td class="form_label">
				{translations name='field.email'}:
			</td>
			<td>
				<input class="input_component" type="text" value="{$formData.email}" name="{$formNames.email}" autocomplete='off' />
				{include file=$theme->template('component.form_help.tpl') structureType=$element->structureType name="email"}
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
