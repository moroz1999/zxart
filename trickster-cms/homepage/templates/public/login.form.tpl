{*where is this file used?*}
{assign var='formData' value=$element->getFormData()}
{assign var='formErrors' value=$element->getFormErrors()}
{assign var='formNames' value=$element->getFormNames()}

{capture assign="moduleContent"}
	{if $element->title}
		{capture assign='moduleTitle'}
			{$element->title}
		{/capture}
	{/if}
	<div class='login_short'>
		{if !$element->displayForm()}
			<div class='login_status_message'>
				{if $element->getUserDataForm()}
					{translations name='login.welcome'}, <a href="{$element->getUserDataFormUrl()}">{$currentUser->getName()}</a>
					{else}
					{translations name='login.welcome'}, <span>{$currentUser->getName()}</span>
				{/if}
			</div>

			<a class='button login_status_logout' href='{$element->URL}id:{$element->id}/action:logout'>{translations name='login.logout'}</a>
		{else}
			<div class="login_component{if $formErrors.userName || $formErrors.password} form_error{/if}">
				<form action="{$controller->fullURL}" class='login_form' method="post" enctype="multipart/form-data" role="form">
					<input class="input_component" type="text" value="" name="{$formNames.userName}" placeholder="{translations name='login.email'}"/>
					<input class="input_component{if $formErrors.userName} form_error{/if}" type="password" value="" name="{$formNames.password}" placeholder="{translations name='login.password'}"/>
					<div class="login_remember">
						<label>
							<input class="login_remember_checkbox checkbox_placeholder" type="checkbox" name='{$formNames.remember}' value="1" {if $formData.remember == 1 || is_null($formData.remember)}checked="checked"{/if} />
							{translations name='login.remember'}
						</label>
					</div>
					<input class="login_form_button button" type="submit" value="{translations name='login.submit'}"/>
					<div class='login_form_bottom'>
						{if $element->getPasswordReminderForm()}
							<a href="{$element->getPasswordReminderFormUrl()}" class='login_forgottenpassword'>{translations name='login.passwordreminder'}</a>
						{/if}
						<input type="hidden" value="{$element->id}" name="id"/>
						<input type="hidden" value="login" name="action"/>
					</div>
				</form>
			</div>
			{if $element->getRegistrationForm()}
				<a href="{$element->getRegistrationFormUrl()}" class='button login_form_register'>{translations name='login.register'}</a>
			{/if}
		{/if}
	</div>
{/capture}

{assign moduleClass "login_component"}
{assign moduleContentClass "login_contents"}

{include file=$theme->template("component.contentmodule.tpl")}