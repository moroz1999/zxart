<div class="content_list_block">
	{if isset($pager)}
		{include file=$theme->template("pager.tpl") pager=$pager}
	{/if}

	<form class="content_list_form" action="{$currentElement->getFormActionURL()}" method="post" enctype="multipart/form-data">

		{if $currentElement->getAllowedTypes()}
			<div class='controls_block content_list_controls'>
				<input type="hidden" class="content_list_form_id" value="{$rootElement->id}" name="id" />
				<input type="hidden" class="content_list_form_action" value="deleteElements" name="action" />

				{include file=$theme->template('block.buttons.tpl') allowedTypes=$currentElement->getAllowedTypes()}
			</div>
		{/if}
		{include file=$theme->template('shared.contentTable.tpl')}
	</form>
	{if $currentElement->getChildrenList()}
		<div class="content_list_bottom">
			{if isset($pager)}
				{include file=$theme->template("pager.tpl") pager=$pager}
			{/if}
		</div>
	{/if}
</div>