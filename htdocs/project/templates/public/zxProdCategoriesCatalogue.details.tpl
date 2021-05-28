{capture assign="moduleTitle"}{if $element->title}{$element->title}{/if}{/capture}
{capture assign="moduleContent"}
    <script>
        window.elementsData = window.elementsData ? window.elementsData : {};
        window.elementsData[{$element->id}] = {$element->getJsonInfo('zxProdsList')};
    </script>
    <app-zx-prods-list element-id="{$element->id}" structure-type="{$element->structureType}"></app-zx-prods-list>
{/capture}

{assign moduleClass "zxprodcategoriescatalogue_details"}
{assign moduleAttributes ""}
{assign moduleTitleClass ""}
{assign moduleContentClass "zxprodcategoriescatalogue_categories"}

{include file=$theme->template("component.contentmodule.tpl")}