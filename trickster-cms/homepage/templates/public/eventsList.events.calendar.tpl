{assign "eventsCalendar" $element->getEventsCalendar()}
{assign "monthsInfoIndex" $element->getEventsMonthsInfoIndex()}
{$weekDayNumbers = [1,2,3,4,5,6,7]}

{foreach $eventsCalendar as $calendarMonth}
	{assign "monthInfo" $monthsInfoIndex.{$calendarMonth@key}}
	<h2>
		{translations name='calendar.month_'|cat:$monthInfo.month} {$monthInfo.year}
	</h2>
	<table class="eventslist_calendar table_component">
		<thead>
		<tr>
			{foreach $weekDayNumbers as $weekDayNumber}
				<th>{translations name='calendar.weekday_'|cat:$weekDayNumber}</th>
			{/foreach}
		</tr>
		</thead>
		<tbody>
		{foreach $calendarMonth as $calendarWeek}
			<tr class="eventslist_calendar_week">
				{foreach $weekDayNumbers as $weekDayNumber}
					{$dayItem = null}
					{if isset($calendarWeek.$weekDayNumber)}
						{$dayItem = $calendarWeek.$weekDayNumber}
					{/if}
					<td class="eventslist_calendar_day{if !$dayItem} eventslist_calendar_day_foreign{/if}{if $dayItem && $dayItem.events} eventslist_calendar_day_featured{/if}{if $dayItem && $dayItem.pastDay} eventslist_calendar_day_past{/if}{if $dayItem && $dayItem.currentDay} eventslist_calendar_day_current{/if}{if $dayItem && $dayItem.holiday} eventslist_calendar_day_holiday{/if}">
						{if $dayItem}
							<div class="eventslist_calendar_day_number">
								{$dayItem.dayNumber}
							</div>
							{if $dayItem.events}
								<div class="eventslist_calendar_day_events">
									{foreach $dayItem.events as $event}
										<div class="eventslist_calendar_day_event">
											<a class="eventslist_calendar_day_event_link" href="{$event->URL}">
												{$event->title}
											</a>

											<div class="eventslist_calendar_day_event_address">
												{$event->city}
											</div>
										</div>
									{/foreach}
								</div>
							{/if}
						{/if}
					</td>
				{/foreach}
			</tr>
		{/foreach}
		</tbody>
	</table>
{/foreach}
