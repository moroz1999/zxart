{if $element->title}{include file=$theme->template('component.heading.tpl') value=$element->title}{/if}
{include file=$theme->template('component.zxProdCategories_list.tpl')}
{include file=$theme->template("component.letters.tpl") lettersInfo=$element->getLettersSelectorInfo()}
{include file=$theme->template("component.years.tpl") yearsInfo=$element->getYearsSelectorInfo()}
<br>
<br>
{if $prods = $element->getProds()}
{*    {include file=$theme->template("pager.tpl") pager=$element->getPager()}*}
    {foreach $prods as $prod}
        {include file=$theme->template('zxProd.short.tpl') element=$prod}
    {/foreach}
{*    {include file=$theme->template("pager.tpl") pager=$element->getPager()}*}
{/if}