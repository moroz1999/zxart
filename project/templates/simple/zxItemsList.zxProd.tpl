{if $element->title}{include file=$theme->template('component.heading.tpl') value=$element->title}{/if}
{foreach $element->getItemsList() as $prod}{include file=$theme->template('zxProd.short.tpl') element=$prod}{/foreach}
{include file=$theme->template("component.hr.tpl") symbol="-"}<br><br><br>