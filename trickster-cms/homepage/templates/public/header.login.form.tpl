{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
{if $formErrors.password}<div class="login_form_error_msg" role="alert">{translations name='login.error'}</div>{/if}
<form action="{$controller->fullURL}" class='login_form' method="post" enctype="multipart/form-data" role="form">
	<div class="login_popup_credentials">
		<div class="login_popup_credentials_label">
			{translations name='login.email'}:
		</div>
		<input class="input_component login_form_input login_form_input_username{if $formErrors.userName} login_error{/if}" type="text" value="" name="{$formNames.userName}"/>
	</div>

	<div class="login_popup_credentials">
		<div class="login_popup_credentials_label">
			{translations name='login.password'}:
		</div>
		<input class="input_component login_form_input login_form_input_pass{if $formErrors.password} login_error{/if}" type="password" value="" name="{$formNames.password}"/>
	</div>

	<div class="login_remember">
		<label>
			{translations name='login.remember'}
			<input class="login_remember_checkbox checkbox_placeholder" type="checkbox" name='{$formNames.remember}' value="1" {if $formData.remember == 1 || is_null($formData.remember)}checked="checked"{/if} />
		</label>
	</div>

	<div class="popup_component_controls">
		<button class='button login_popup_button'>
			<span class='button_text'>{translations name='login.login'}</span>
		</button>
	</div>

	<input type="hidden" value="{$element->id}" name="id" />
	<input type="hidden" value="login" name="action" />
</form>

{if $element->getRegistrationForm()}
	<div class="popup_component_notice">
		<a href="{$element->getRegistrationFormUrl()}" class='header_login_register_link'>{translations name='login.register'}</a>
		{if $element->getPasswordReminderForm()}
			<br />
			<a href="{$element->getPasswordReminderFormUrl()}" class='header_login_recovery_link'>{translations name='login.passwordreminder'}</a>
		{/if}
	</div>
{/if}