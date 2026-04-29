{if !empty($form)}
	{$controls = $form->getAdditionalControls()}
{else}
	{$controls = true}
{/if}

{if $element->hasActualStructureInfo()}
	<div class="content_list_block">
		<form class="content_list_form" action="{$currentElement->getFormActionURL()}" method="post" enctype="multipart/form-data">
			{if $controls}
{*				{if $currentElement->getAllowedTypes($currentElement->getActionName()) || !empty($actionButtons)}*}
					<div class='controls_block content_list_controls'>
						{if isset($formElement)}{$elementId=$formElement->id}{else}{$elementId=$rootElement->id}{/if}
						<input type="hidden" class="content_list_form_id" value="{$elementId}" name="id" />
						<input type="hidden" class="content_list_form_action" value="" name="action" />

						{include file=$theme->template('block.buttons.tpl') allowedTypes=$currentElement->getAllowedTypes($currentElement->getActionName())}
					</div>
{*				{/if}*}
			{/if}
			{if !empty($form) && !empty($form->getAdditionalContentTable())}
				{include file=$theme->template($form->getAdditionalContentTable())}
			{else}
				{include file=$theme->template('shared.contentTable.tpl')}
			{/if}
		</form>
		<div class="content_list_bottom">
			{if isset($pager) && $currentElement->getChildrenList()}
				{include file=$theme->template("pager.tpl") pager=$pager}
			{/if}
		</div>
	</div>
{/if}