{if !isset($action)}
	{assign 'action' 'receive'}
{/if}
<div class="controls_block form_controls editing_controls">
	<input type="hidden" value="{$element->id}" name="id" />
	<input type="hidden" value="{$action}" name="action" />

	<input class="button button_green" type="submit" value='{translations name='button.save'}'/>

{*	{if $element->hasActualStructureInfo() && isset($currentElementPrivileges.publicDelete)}*}
{*		<a class='button delete_button' href="{$element->URL}id:{$currentElement->id}/action:publicDelete/">{translations name='controls.delete'}</a>*}
{*	{/if}*}

	<div class="clearfix"></div>
</div>