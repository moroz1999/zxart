{if $element->title}{include file=$theme->template('component.heading.tpl') value=$element->title}{/if}
{if $parties = $element->getRecentParties()}{foreach $parties as $party}{include file=$theme->template('party.short.tpl') element=$party}{/foreach}{/if}
{include file=$theme->template("component.hr.tpl") symbol="-"}<br><br><br>