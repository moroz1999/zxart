{if !isset($action)}
    {assign 'action' 'receive'}
{/if}
<div class="controls_block form_controls">
    <input type="hidden" value="{$element->id}" name="id" />
    <input type="hidden" value="{$action}" name="action" />

    <input class="button button success_button" type="submit" value='{translations name='button.save'}'/>

    <div class="clearfix"></div>
</div>