<div class="adminlogin_component">
	<div class="adminlogin_component_content">
		{assign var='formData' value=$element->getFormData()}
		{assign var='formErrors' value=$element->getFormErrors()}
		{assign var='formNames' value=$element->getFormNames()}
		<form action="{$controller->fullURL}" class="form_component" method="post" enctype="multipart/form-data">
			<div class="form_fields">
				<div class="form_items">
					<div class="form_field{if $formErrors.password} form_error{/if}">
						<input class='input_component adminlogin_input' type="text" placeholder="{translations name='field.username'}" value="{$formData.userName}" name="{$formNames.userName}" id="{$formNames.userName}" />
						<span class="icon icon_username"></span>
					</div>
				</div>
				<div class="form_items">
					<div class="form_field{if $formErrors.password} form_error{/if}">
						<input class='input_component adminlogin_input' type="password" placeholder="{translations name='field.password'}" value="{$formData.password}" name="{$formNames.password}" />
						<span class="icon icon_password"></span>
					</div>
				</div>
				{*<div class="form_items remember_me" {if $formErrors.password}class="form_error"{/if}>*}
					{*<div class="form_field">*}
						{*<input class="checkbox_placeholder" type="checkbox" value="1" name="{$formNames.$fieldName}"{if $formData.$fieldName} checked="checked"{/if} />*}
						{*<span class="remind_me">{translations name="{$translationGroup}.{strtolower($fieldName)}"}</span>*}
					{*</div>*}
				{*</div>*}
				<div class="adminlogin_controls">
					<button class="button warning_button login_button" type="submit">
						<span class="button_text">{translations name='button.login'}</span>
						<span class="icon icon_login"></span>
					</button>
					<input type="hidden" value="{$element->id}" name="id" />
					<input type="hidden" value="login" name="action" />
				</div>
			</div>
		</form>
	</div>
	<div class="adminlogin_component_hr"></div>
</div>