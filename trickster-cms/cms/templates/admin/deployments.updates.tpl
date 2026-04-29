<div class="deployments_updates_main">
	{if $installError}
		{include file=$theme->template('block.error.tpl') message=$installError}
	{/if}
	{if isset($currentElementPrivileges.installUpdates)}
		{if $installed}
			<div class="deployments_update_success_msg">
				{translations name='deployments.update_success'}
			</div>
		{/if}
		{if $updates}
			<a href="{$element->URL}id:{$currentElement->id}/action:installUpdates/" class="button primary_button">{translations name='deployments.button_install_updates'}</a>
		{/if}
	{/if}
</div>
{include file=$theme->template('deployments.list.tpl') list=$updates}