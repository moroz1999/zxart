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
        <zx-prods-list element-id="{$element->id}" property="prods"></zx-prods-list>
        {if $url = $element->getCatalogueUrl()}
            <div class="zxitemslist_controls">
                <a class="zxitemslist_link button" href="{$url}">{$element->buttonTitle}</a>
            </div>
        {/if}
    {/capture}
    {assign moduleClass "zxitemslist zxitemslist_zxprod"}
    {assign moduleTitleClass ""}
    {assign moduleContentClass ""}

    {include file=$theme->template("component.contentmodule.tpl")}
{/if}