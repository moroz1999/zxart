{capture assign="moduleContent"}
	<a href="{$element->getUrl()}">
		<img loading="lazy" class="party_short_image" src="{$element->getImageUrl('partyShort')}"/>
	</a>
{/capture}
{assign moduleClass "party_short"}
{assign moduleAttributes ""}
{assign moduleTitle $element->getTitle()}
{assign moduleTitleClass "party_short_title"}
{assign moduleContentClass ""}
{include file=$theme->template("component.subcontentmodule_square.tpl")}