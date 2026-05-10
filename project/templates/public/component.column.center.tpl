<div class="center_column" role="main">
    {if $firstPageElement->final}
        <zx-firstpage></zx-firstpage>
    {else}
        {include file=$theme->template("component.breadcrumbs.tpl")}
        {include file=$theme->template("component.letters.tpl")}
        {include file=$theme->template("component.years.tpl")}
        {include file=$theme->template($currentElement->getTemplate()) element=$currentElement}
    {/if}
</div>
