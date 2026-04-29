<div class="center_column" role="main">
	{include file=$theme->template("component.breadcrumbs.tpl")}
	<div class="grid_container">
		{include file=$theme->template($currentElement->getTemplate()) element=$currentElement}
	</div>
</div>
