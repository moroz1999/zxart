<div class="content_list_block">
	<form class="content_list_form" action="{$currentElement->getFormActionURL()}" method="post" enctype="multipart/form-data">
		<div class='controls_block content_list_controls'>
			<input type="hidden" class="content_list_form_id" value="{$rootElement->id}" name="id" />
			<input type="hidden" class="content_list_form_elementid" value="{$currentElement->id}" />
			<input type="hidden" class="content_list_form_action" value="deleteElements" name="action" />

			{include file=$theme->template('block.buttons.tpl') allowedTypes=$currentElement->getAllowedTypes()}
		</div>
		{include file=$theme->template('shared.contentTable.tpl') contentList=$element->getRedundantTranslations()}
	</form>

</div>