{if $element->title}{include file=$theme->template('component.heading.tpl') value=$element->title}{/if}
{include file=$theme->template("component.partyinfo.tpl")}

{foreach from=$element->getProdsCompos() key=compoType item=compo}
	<br>{include file=$theme->template("component.hr.tpl") symbol="-"}<br><br>
	{assign "compoTitle" "compo_"|cat:$compoType}<b>{translations name='label.compo'}: {translations name="party.$compoTitle"}</b><br><br>
	{foreach from=$compo item=prod}{include file=$theme->template("zxProd.short.tpl") element=$prod}{/foreach}
{/foreach}
{foreach from=$element->getPicturesCompos() key=compoType item=compo}
	<br>{include file=$theme->template("component.hr.tpl") symbol="-"}<br><br>
	{assign "compoTitle" "compo_"|cat:$compoType}<b>{translations name='label.compo'}: {translations name="zxPicture.$compoTitle"}</b><br><br>
	{foreach from=$compo item=prod}{include file=$theme->template("zxPicture.short.tpl") element=$prod}{/foreach}
{/foreach}
{foreach from=$element->getTunesCompos() key=compoType item=compo}
	<br>{include file=$theme->template("component.hr.tpl") symbol="-"}<br><br>
	{assign "compoTitle" "compo_"|cat:$compoType}<b>{translations name='label.compo'}: {translations name="musiccompo.$compoTitle"}</b><br><br>
	{include file=$theme->template("component.musictable.tpl") musicList=$compo element=$element}
{/foreach}
{include $theme->template('component.comments.tpl')}