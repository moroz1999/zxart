<div class="content_list_block">
	{if isset($element->pager)}
		{include file=$theme->template("pager.tpl") pager=$element->pager}
	{/if}

	<form class="content_list_form" action="{$currentElement->getFormActionURL()}" method="post" enctype="multipart/form-data">

		{if $currentElement->getAllowedChildStructureTypes("showForm")}
			<div class='controls_block content_list_controls'>
				<input type="hidden" value="{$rootNode->id}" name="id" />
				<input type="hidden" class="content_list_form_action" value="deleteElements" name="action" />

				{include file=$theme->template('component.buttons.tpl') allowedTypes=$currentElement->getAllowedChildStructureTypes("showForm")}
			</div>
		{/if}
		{include file=$theme->template('shared.contentTable.tpl')}
	</form>
	<div class="below_content">
		{if isset($element->pager)}
			{include file=$theme->template("pager.tpl") pager=$element->pager}
		{/if}
	</div>
</div>