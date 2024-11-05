{capture assign="moduleContent"}
	<a href="{$element->getUrl()}" class="zxprod_short_images">
        {include file=$theme->template('component.elementimage.tpl') src=$element->getImageUrl(0) class='zxprod_short_image' lazy=true}
        {if $element->getImageUrl(1)}
            {include file=$theme->template('component.elementimage.tpl') src=$element->getImageUrl(1) class='zxprod_short_image_second' lazy=true}
        {/if}
	</a>
    <span class="zxprod_short_title">
        <div class="zxprod_short_title_controls">
            {include file=$theme->template("component.votecontrols.tpl") element=$element}
            {include file=$theme->template("component.playlist.tpl") element=$element}
        </div>
        <a href="{$element->getUrl()}">{$element->getTitle()}</a>
    </span>
    {if $element->getPartyElement()}
        <div class='zxprod_short_party'>
            {if $element->partyplace=='1'}<img loading="lazy" src="{$theme->getImageUrl("gold_cup.png")}" alt='{translations name='label.firstplace'}'/>{/if}
            {if $element->partyplace=='2'}<img loading="lazy" src="{$theme->getImageUrl("silver_cup.png")}" alt='{translations name='label.secondplace'}'/>{/if}
            {if $element->partyplace=='3'}<img loading="lazy" src="{$theme->getImageUrl("bronze_cup.png")}" alt='{translations name='label.thirdplace'}'/>{/if}
            <a href='{$element->getPartyElement()->URL}'>{$element->getPartyElement()->title}</a>{if $element->partyplace > 0}({$element->partyplace}){/if}
        </div>
    {/if}
    <script>
		if (!window.prodsList) window.prodsList = [];
		window.prodsList.push({$element->getJsonInfo('list')});
    </script>
{/capture}
{$moduleSubContent = $moduleContent}
{assign moduleClass "zxprod_short"}
{assign moduleAttributes ""}
{assign moduleTitle ""}
{assign moduleTitleClass "zxprod_short_title"}
{assign moduleContentClass ""}
{include file=$theme->template("component.subcontentmodule_square.tpl")}