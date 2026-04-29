{stripdomspaces}
<div class='login_component header_login'>
{if !$element->displayForm()}
	<span class='login_status_message'>
		{if $element->getUserDataForm()}
			{translations name='login.welcome'},&#160;<a class="login_status_profile_link" href="{$element->getUserDataFormUrl()}">{$currentUser->getName()}</a>
		{else}
			{translations name='login.welcome'},&#160;<span>{$currentUser->getName()}</span>
		{/if}
	</span>&#160;<a class='button login_status_logout' href='{$element->URL}id:{$element->id}/action:logout'>{translations name='login.logout'}</a>
{else}
	<div class='login_short'>
		{if $element->getRegistrationForm()}
			<a href="{$element->getRegistrationFormUrl()}" class='login_form_register'>{translations name='login.register'}</a>
		{/if}
		<a href="{$controller->fullURL}" class='button login_short_button'>
			<span class='button_text'>{translations name='login.login'}</span>
		</a>
	</div>
	<div class='login_form_block login_form_popup popup_component'>
		<div class="login_form_container">
			{if $element->description}
				<div class="login_form_description html_content">
					{$element->description}
				</div>
			{/if}
			{include $theme->template('header.login.form.tpl')}
		</div>
	</div>
{/if}
</div>
{/stripdomspaces}