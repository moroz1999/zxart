{capture assign="moduleTitle"}{if $element->title}{$element->title}{/if}{/capture}
{capture assign="moduleContent"}
    {include file=$theme->template('component.zxProdCategories_list.tpl')}
{/capture}

{assign moduleClass "zxprodcategoriescatalogue_details"}
{assign moduleAttributes ""}
{assign moduleTitleClass ""}
{assign moduleContentClass "zxprodcategoriescatalogue_categories"}

{include file=$theme->template("component.contentmodule.tpl")}