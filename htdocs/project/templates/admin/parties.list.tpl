{*{include file=$theme->template("component.new_element.tpl") allowedTypes=$currentElement->allowedTypes newElementURL=$currentElement->URL}*}
{*{include file=$theme->template("list_table.tpl")}*}

<div class="content_list_block">
	{if isset($pager)}
		{include file=$theme->template("pager.tpl") pager=$pager}
	{/if}

	<form class="content_list_form" action="{$currentElement->getFormActionURL()}" method="post" enctype="multipart/form-data">

		{if $currentElement->getAllowedTypes("showForm")}
			<div class='controls_block content_list_controls'>
				<input type="hidden" value="{$rootNode->id}" name="id" />
				<input type="hidden" class="content_list_form_action" value="deleteElements" name="action" />

				{include file=$theme->template('component.buttons.tpl') allowedTypes=$currentElement->getAllowedTypes("showForm")}
			</div>
		{/if}
		{include file=$theme->template('shared.contentTable.tpl')}
	</form>
	<div class="below_content">
		{if isset($pager) && $currentElement->getChildrenList()}
			{include file=$theme->template("pager.tpl") pager=$pager}
		{/if}
	</div>
</div>