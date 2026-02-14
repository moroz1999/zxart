{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}
<div class='login_component login_header'>
	{if !$element->displayForm()}
		<div class='login_status_message'>
			{if $element->getUserDataForm()}
				{translations name='login.welcome'}, <a href="{$element->getUserDataFormUrl()}">{if $currentUser->firstName}{$currentUser->firstName} {$currentUser->lastName}{else}{$currentUser->userName}{/if}</a>
				{else}
				{translations name='login.welcome'}, <span>{if $currentUser->firstName}{$currentUser->firstName} {$currentUser->lastName}{else}{$currentUser->userName}{/if}</span>
			{/if}
			{if isset($playlistsElement)}
				<div><a href="{$playlistsElement->URL}">{$playlistsElement->title}</a></div>
			{/if}
		</div>

		<a class='button login_status_logout' href='{$controller->fullURL}/id:{$element->id}/action:logout'>{translations name='login.logout'}</a>
	{else}
		<div class="">
			{if $element->errorMessage}{$element->errorMessage}{/if}
			<form action="{$controller->fullURL}" class='login_form' method="post" enctype="multipart/form-data">
				<input class="input_component{if $formErrors.userName} form_error{/if}" type="text" value="" name="{$formNames.userName}" placeholder="{translations name='login.user'}"/>
				<input class="input_component{if $formErrors.password} form_error{/if}" type="password" value="" name="{$formNames.password}" placeholder="{translations name='login.password'}"/>
				<div class="login_remember">
					<label>
						<input class="login_remember_checkbox checkbox_placeholder" type="checkbox" name='{$formNames.remember}' value="1" {if $formData.remember == 1 || $formData.remember === null}checked="checked"{/if} />
						{translations name='login.remember'}
					</label>
				</div>
				<input class="login_form_button button button_primary" type="submit" value="{translations name='login.submit'}"/>
				<div class="login_form_actions">
					{if $element->getRegistrationForm()}
						<a href="{$element->getRegistrationFormUrl()}" class='login_form_register'>{translations name='login.register'}</a>
					{/if}
					{if $element->getPasswordReminderForm()}
						<a href="{$element->getPasswordReminderFormUrl()}" class='login_forgottenpassword'>{translations name='login.passwordreminder'}</a>
					{/if}
				</div>
				<input type="hidden" value="{$element->id}" name="id"/>
				<input type="hidden" value="login" name="action"/>
			</form>
		</div>
	{/if}
</div>
