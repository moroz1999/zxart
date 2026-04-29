{if $h1 = $element->getH1()}
	{capture assign="moduleTitle"}
		{$h1}
	{/capture}
{elseif $element->title}
	{capture assign="moduleTitle"}
		{$element->title}
	{/capture}
{/if}
{capture assign="moduleContent"}
	<div>
		<div class="event_details_main_block">
			{if $element->originalName}
				<div class="event_details_image_wrap">
					{include file=$theme->template('component.elementimage.tpl') type='eventDetails' class='event_details_image'}
				</div>
			{/if}

			<div class="event_details_specs">
				<div class="event_details_spec event_details_dates">
					{if $element->startDate}
						<div class="event_details_date">
							{translations name='event.startdate'}: {$element->startDate} {$element->startTime}
						</div>
					{/if}
					{if $element->endDate}
						<div class="event_details_date">
							{translations name='event.enddate'}: {$element->endDate} {$element->endTime}
						</div>
					{/if}
				</div>
				{if $element->country}
					<div class="event_details_spec event_details_country">
						{translations name='event.country'}: {$element->country}
					</div>
				{/if}
				{if $element->city}
					<div class="event_details_spec event_details_city">
						{translations name='event.city'}: {$element->city}
					</div>
				{/if}
				{if $element->address}
					<div class="event_details_spec event_details_address">
						{translations name='event.address'}: {$element->address}
					</div>
				{/if}

				{if $element->link}
					<div class="event_details_spec event_details_link">
						{translations name='event.website'}:
						<a href="{$element->link}" target="_blank">{$element->link}</a>
					</div>
				{/if}
			</div>
		</div>

		<div class='event_details_content html_content'>
			{if $element->description}{$element->description}{else}{$element->introduction}{/if}
		</div>

		{if $element->mapCode}
			<div class="event_details_map">
				{$element->mapCode}
			</div>
		{/if}
	</div>
{/capture}

{assign moduleClass "event_details"}
{assign moduleTitleClass "event_details_title"}
{include file=$theme->template("component.contentmodule.tpl")}
