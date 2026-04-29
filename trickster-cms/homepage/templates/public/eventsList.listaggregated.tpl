<table class="eventslist_table eventslist_table_aggregated table_component">
	<thead>
		<tr>
			<th>
				{translations name='eventslist.month'}
			</th>
			<th>
				{translations name='eventslist.date'}
			</th>
			<th>
				{translations name='eventslist.location'}
			</th>
			<th>
				{translations name='eventslist.title'}
			</th>
		</tr>
	</thead>
	<tbody>
	{if !$list}
		{foreach $events as $event}

			<tr>

					{if $event@first}
						<td class="eventslist_table_item_month" {if $events|@count > 1}rowspan="{$events|@count}{/if}">
							{translations name='calendar.month_'|cat:$dateInfo.month} {$dateInfo.year}
						</td>
					{/if}

				<td class="eventslist_table_item_date">
					{$event->startDate}
					{if $event->endDate && ($event->endDate != $event->startDate)}
						- {$event->endDate}
					{/if}
				</td>
				<td class="eventslist_table_item_city">
					{if $event->city}
						<span>{$event->city}, </span>
					{/if}
					<span>{$event->country}</span>
				</td>
				<td class="eventslist_table_item_title">
					<a class="eventslist_table_item_title_link" href="{$event->URL}"><span>{$event->title}</span></a>
				</td>
			</tr>
		{/foreach}
	{else}
		{foreach $events as $groupedEvents}
			{foreach $groupedEvents as $event}
				{assign "monthInfo" $dateInfo.{$groupedEvents@key}}
				<tr>
					{if $event@first}
						<td class="eventslist_table_item_month" {if $groupedEvents|@count > 1}rowspan="{$groupedEvents|@count}{/if}">
							{translations name='calendar.month_'|cat:$monthInfo.month} {$monthInfo.year}
						</td>
					{/if}
					<td class="eventslist_table_item_date">
						{$event->startDate}
						{if $event->endDate && ($event->endDate != $event->startDate)}
							- {$event->endDate}
						{/if}
					</td>
					<td class="eventslist_table_item_city">
						{if $event->city}
							<span>{$event->city}, </span>
						{/if}
						<span>{$event->country}</span>
					</td>
					<td class="eventslist_table_item_title">
						<a class="eventslist_table_item_title_link" href="{$event->URL}"><span>{$event->title}</span></a>
					</td>
				</tr>
			{/foreach}
		{/foreach}
	{/if}
	</tbody>
</table>