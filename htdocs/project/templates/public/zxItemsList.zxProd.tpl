{if $currentUser->userName != 'anonymous' || $element->requiresUser == 0}
    {if $element->title}
        {capture assign="moduleTitle"}
            {$element->title}
        {/capture}
    {/if}
    {capture assign="moduleContent"}
        <script>
            window.elementsData = window.elementsData ? window.elementsData : { };
            window.elementsData[{$element->id}] = {$element->getJsonInfo('zxProdsList')};
        </script>
        <app-zx-prods-list element-id="{$element->id}"></app-zx-prods-list>
        {*		{if $zxprodDetailedSearchElement && $element->searchFormParametersString}*}
        {*			<div class="zxitemslist_controls">*}
        {*				<a class="zxitemslist_link button" href="{$zxprodDetailedSearchElement->URL}{$element->searchFormParametersString}">{translations name='zxitemslist.seemorezxprod'}</a>*}
        {*			</div>*}
        {*		{/if}*}
    {/capture}
    {assign moduleClass "zxitemslist zxitemslist_zxprod"}
    {assign moduleTitleClass ""}
    {assign moduleContentClass ""}

    {include file=$theme->template("component.contentmodule.tpl")}
{/if}