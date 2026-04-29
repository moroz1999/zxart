{assign moduleTitle $element->title}

{if $element->originalName != ""}
	{capture assign="moduleSideContent"}
		{include file=$theme->template('component.elementimage.tpl') type='serviceShortImage' class='service_short_image' lazy=true}
	{/capture}
{/if}
{capture assign="moduleContent"}
	<div class="service_short_header">
		<span class='service_short_content html_content'>
			{$element->introduction}
		</span>
	</div>
{/capture}

{capture assign="moduleControls"}
	<a class="button service_short_readmore" href="{$element->URL}">
		<span class='button_text'>{translations name='service.short_readmore'}</span>
	</a>
{/capture}

{assign moduleClass "service_short_block"}
{assign moduleTitleClass "service_short_heading"}
{assign moduleAttributes ''}
{assign moduleSideContentClass "service_short_image_wrap"}
{include file=$theme->template("component.subcontentmodule_wide.tpl")}