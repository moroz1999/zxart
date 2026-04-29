{assign moduleTitle $element->title}

{if $element->originalName}
	{capture assign="moduleSideContent"}
		{include file=$theme->template('component.elementimage.tpl') type='productionShort' class='production_short_image' lazy=true}
	{/capture}
{/if}
{capture assign="moduleContent"}
	<div class="production_short_top_block">
		<div class='production_short_content html_content'>
			{$element->introduction}
		</div>
	</div>
{/capture}
{capture assign="moduleControls"}
	<a class="button production_short_readmore" href="{$element->URL}">
		<span class='button_text'>{translations name='production.readmore'}</span>
	</a>
{/capture}

{assign moduleClass "production_short"}
{assign moduleTitleClass "production_short_title"}
{assign moduleAttributes ''}
{assign moduleSideContentClass "production_short_image_wrap"}
{include file=$theme->template("component.subcontentmodule_wide.tpl")}
