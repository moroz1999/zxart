{capture assign="moduleTitle"}<span class="event_detailed_dates">{$element->startDate}{if $element->endDate} - {$element->endDate}{/if}</span> {if $element->title}{$element->title}{/if}{/capture}
{if $element->originalName}
	{capture assign="moduleSideContent"}
		<div>
			{include file=$theme->template('component.elementimage.tpl') type='eventDetailed' class='event_detailed_image' lazy=true}
		</div>
{/capture}
{/if}
{capture assign="moduleContent"}
	<div class='event_detailed_description'>
		{if $element->introduction}
			<div class="event_detailed_introduction">
				{$element->introduction}
			</div>
		{/if}
		<div class="event_detailed_location">
            {$element->city}
        </div>
	</div>
{/capture}
{capture assign="moduleControls"}
	{if $element->description}
	<a class="event_detailed_link" href='{$element->URL}'>
		<span class='event_detailed_link_text'>
			{translations name='event.readmore'}
		</span>
	</a>
	{/if}
{/capture}
{assign moduleClass "event_detailed"}
{assign moduleTitleClass "event_detailed_title"}
{assign moduleSideContentClass "event_detailed_image_wrap"}
{include file=$theme->template("component.subcontentmodule_wide.tpl")}