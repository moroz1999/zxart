<div class="event_column">
	<div class="event_column_title">{if $element->title}{$element->title}{/if}</div>
	<div class='event_column_description'>
        <div class="event_column_dates">{$element->startDate}{if $element->endDate} - {$element->endDate}{/if}</div>

		<div class="event_column_location">
            {$element->city}
        </div>
		<a class="event_column_link" href='{$element->URL}'>
		<span class='event_column_link_text'>
			{translations name='event.readmore'}
		</span>
		</a>
	</div>
</div>
