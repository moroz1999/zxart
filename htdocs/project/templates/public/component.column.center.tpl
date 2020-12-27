<div class="center_column" role="main">
    {include file=$theme->template("component.breadcrumbs.tpl")}
    {include file=$theme->template("component.letters.tpl")}
	{include file=$theme->template("component.years.tpl")}
	{include file=$theme->template($currentElement->getTemplate()) element=$currentElement}
</div>