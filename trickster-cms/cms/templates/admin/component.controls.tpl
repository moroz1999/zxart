{if !isset($action)}
	{assign 'action' 'receive'}
{/if}
{if !empty($form)}
	{$buttons = $form->getControls()}
    {$element = $form->getElement()}
{else}
	{$buttons = [
			'save' => [
			'class' => 'success_button',
			'type' => 'submit'
		],
			'delete' => [
			'class' => 'warning_button',
			'action' => 'delete',
			'icon' => 'delete',
			'confirmation' => 'message.deleteelementconfirmation'
		]
	]}
{/if}
<div class="controls_block form_controls">
	<input type="hidden" value="{$element->id}" name="id" />
	<input type="hidden" value="{$action}" name="action" />
	{foreach $buttons as $key=>$control}
		<button class="button {$control.class}"
			{if !empty($control.type)} type="{$control.type}" {else} type="button"{/if}
			{if !empty($control.action)} data-action="{$control.action}"{/if}
			{if !empty($control.confirmation)} data-confirmation="{translations name="{$control.confirmation}"}"{/if}
		>
			{translations name="button.{$key}"}
			{if !empty($control.icon)}<span class="icon icon_{$control.icon}"></span>{/if}
		</button>
	{/foreach}
</div>