{capture assign="moduleTitle"}{if $element->title}{$element->title}{/if}{/capture}
{capture assign="moduleContent"}
    <div class="editing_controls">
        {if isset($privileges.zxProdsUploadForm.batchUploadForm) && $privileges.zxProdsUploadForm.batchUploadForm == true}
            <a class="button button_primary"
               href="{$element->URL}type:zxProdsUploadForm/action:batchUploadForm/">{translations name='zxProdCategory.upload'}</a>
        {/if}
    </div>
    <script>
        window.elementsData = window.elementsData ? window.elementsData : {};
        window.elementsData[{$element->id}] = {$element->getJsonInfo('zxProdsList')};
    </script>
    <zx-prods-category element-id="{$element->id}"></zx-prods-category>
{/capture}

{assign moduleClass "zxprodcategoriescatalogue_details"}
{assign moduleAttributes ""}
{assign moduleTitleClass ""}
{assign moduleContentClass "zxprodcategoriescatalogue_categories"}

{include file=$theme->template("component.contentmodule.tpl")}