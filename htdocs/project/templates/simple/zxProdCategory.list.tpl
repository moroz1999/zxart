{section start=0 loop=$pad name='pad'}...{/section}<a href="{$element->getUrl()}">{$element->title}</a><br>
{include file=$theme->template('component.zxProdCategories_list.tpl') pad=$pad+1}