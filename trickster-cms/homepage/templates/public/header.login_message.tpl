
<span class='login_status_message'>
	{if $loginElement->getUserDataForm()}
		{translations name='login.welcome'},&#160;<a class="login_status_profile_link" href="{$loginElement->getUserDataFormUrl()}">{$currentUser->getName()}</a>
	{else}
		{translations name='login.welcome'},&#160;<span>{$currentUser->getName()}</span>
	{/if}
</span>&nbsp;<a class='button login_status_logout' href='{$loginElement->getUrl('logout')}'>{translations name='login.logout'}</a>