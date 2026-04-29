{assign moduleTitle $element->title}
{if $element->originalName}
	{capture assign="moduleSideContent"}
		<div>
			{include file=$theme->template('component.elementimage.tpl') type='eventShort' class='event_short_image' lazy=true}
		</div>
{/capture}
{/if}
{capture assign="moduleContent"}
	<div class='event_short_description'>
        <div class="event_short_dates">{$element->startDate}{if $element->endDate} - {$element->endDate}{/if}</div>

		<div class="event_short_location">
            {$element->city}
        </div>
	</div>
{/capture}

{capture assign="moduleControls"}
	<a class="event_short_link" href='{$element->URL}'>
		<span class='event_short_link_text'>
			{translations name='event.readmore'}
		</span>
	</a>
{/capture}

{assign moduleClass "event_short"}
{assign moduleTitleClass "event_short_title"}
{assign moduleSideContentClass "event_short_image_wrap"}
{include file=$theme->template("component.subcontentmodule_wide.tpl")}