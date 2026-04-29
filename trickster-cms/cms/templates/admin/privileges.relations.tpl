{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
{assign var='type' value=['User', 'UserGroup']}

<div class="privileges_selections">
	<form class="form_component" action="{$currentElement->getFormActionURL()}" method="post" enctype="multipart/form-data">
		<div class="form_fields">
			<div class="form_items">
				<div class="privileges_selection form_field">
					<select class="dropdown_placeholder" name='{$formNames.userId}' id='userId'>
						<option value="">No User Selected</option>
						{foreach from=$element->usersList item=user}
							<option value='{$user->id}' {if $user->id == $element->userId}selected{/if}>
								{$user->userName}
							</option>
						{/foreach}
					</select>
				</div>
				<div class="form_field">
					<input class="button" type="submit" onclick='document.getElementById("userGroupId").value="";'
						   value='{translations name='privileges.select_user'}'/>
				</div>
			</div>
			<div class="form_items">
				<div class="privilege_selection form_field">
					<select class="dropdown_placeholder" name='{$formNames.userGroupId}' id='userGroupId'>
						<option value="">No UserGroup Selected</option>
						{foreach from=$element->userGroupsList item=group}
							<option value='{$group->id}' {if $group->id == $element->userGroupId}selected{/if}>
								{$group->groupName}
							</option>
						{/foreach}
					</select>
				</div>
				<div class="form_field">
					<input class="button" type="submit" onclick='document.getElementById("userId").value="";'
						   value='{translations name='privileges.select_group'}'/>
				</div>
				<input type="hidden" value="{$currentElement->id}" name="id" />
				<input type="hidden" value="showPrivileges" name="action" />
			</div>
		</div>
	</form>
</div>
{if $element->privileges}
	<div class="privilegesform_component">
		<form action="{$currentElement->getFormActionURL()}"
			  class="privilegesform_holder"
			  method="post"
			  enctype="multipart/form-data">
			<table class="privileges_table form_table">
				<tr class="privileges_table_header">
					<td>
						{translations name='privileges.code'}
					</td>
					<td>
						{translations name='privileges.type'}
					</td>
					<td>
						{translations name='privileges.action'}
					</td>
				</tr>
				{foreach from=$element->privileges item=privilege}
					<tr class="{if $privilege->type=='deny'} denied_privilege
							{elseif $privilege->type=='allow'} allowed_privilege
							{else} privilege{/if}">
						<td>
							<span class="icon icon_{$privilege->module|strtolower}"></span>
							{$privilege->module}
						</td>
						<td>
							{translations name='element.'|cat:$privilege->module}
						</td>
						<td>
							{$privilege->action}
						</td>
						<td>
							<label class="">
								<input type="radio" name="{$privilege->module}/{$privilege->action}" value="allow"
									   {if $privilege->type=='allow'}checked='checked'{/if} />
								{translations name='privileges.allow'}
							</label>
						</td>
						<td>
							<label class="">
								<input type="radio" name="{$privilege->module}/{$privilege->action}" value="deny"
									   {if $privilege->type=='deny'}checked='checked'{/if}/>
								{translations name='privileges.deny'}
							</label>
						</td>
						<td>
							<label class="">
								<input type="radio" name="{$privilege->module}/{$privilege->action}" value="inherit"
									   {if $privilege->type=='inherit'}checked='checked'{/if}/>
								{translations name='privileges.inherit'}
							</label>
						</td>
					</tr>
				{/foreach}
			</table>
		</form>
		<form action="{$currentElement->getFormActionURL()}" class="privilegesform_form" method="post" enctype="multipart/form-data">
			<div class="controls_block">
				<input class="button button success_button" type="submit" value="{translations name='privileges.save'}"/>
				<input class="privileges_json_input" type="hidden" value="" name="{$formNames.json}" />
				<input type="hidden" value="{$element->userId}" name="{$formNames.userId}" />
				<input type="hidden" value="{$element->userGroupId}" name="{$formNames.userGroupId}" />
				<input type="hidden" value="receivePrivileges" name="action" />
				<input type="hidden" value="{$currentElement->id}" name="id" />
			</div>
		</form>
	</div>
{/if}
