{if $element->title}{include file=$theme->template('component.heading.tpl') value=$element->title}{/if}
{include file=$theme->template("component.partiestable.tpl") partiesList=$currentElement->getPartiesList()}
