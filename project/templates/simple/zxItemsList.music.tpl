{if $element->title}{include file=$theme->template('component.heading.tpl') value=$element->title}{/if}
{include file=$theme->template("component.musictable.tpl") musicList=$element->getItemsList() element=$element}
{include file=$theme->template("component.hr.tpl") symbol="-"}<br><br><br>