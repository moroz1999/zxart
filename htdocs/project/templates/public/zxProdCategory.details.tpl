{*{$moduleTitle = $element->getH1()}*}
{*{capture assign="moduleContent"}*}
{*    <div class="editing_controls">*}
{*    {if isset($privileges.zxProdsUploadForm.batchUploadForm) && $privileges.zxProdsUploadForm.batchUploadForm == true}*}
{*        <a class="button" href="{$element->URL}type:zxProdsUploadForm/action:batchUploadForm/">{translations name='zxProdCategory.upload'}</a>*}
{*    {/if}*}
{*    </div>*}
{*    {include file=$theme->template('component.zxProdCategories_list.tpl')}*}
{*    {include file=$theme->template("component.letters.tpl") lettersInfo=$element->getLettersSelectorInfo()}*}
{*    {include file=$theme->template("component.years.tpl") yearsInfo=$element->getYearsSelectorInfo()}*}
    <app-zx-prods-list
            element-id="{$element->id}"
    ></app-zx-prods-list>
{*    {if $prods = $element->getProds()}*}
{*        {include file=$theme->template("pager.tpl") pager=$element->getPager()}*}
{*        <div class="zxprodcategory_details_prods zxprods_list">*}
{*            {foreach $prods as $prod}*}
{*                {include file=$theme->template('zxProd.short.tpl') element=$prod}*}
{*            {/foreach}*}
{*        </div>*}
{*        {include file=$theme->template("pager.tpl") pager=$element->getPager()}*}
{*    {/if}*}
{*{/capture}*}
{*{assign moduleClass "zxprodcategory_details"}*}
{*{assign moduleAttributes ""}*}
{*{assign moduleTitleClass ""}*}
{*{assign moduleContentClass ""}*}
{*{include file=$theme->template("component.contentmodule.tpl")}*}