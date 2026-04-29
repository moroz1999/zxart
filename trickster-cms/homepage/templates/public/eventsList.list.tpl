
<table class="eventslist_table table_component">
	<thead>
	<tr>
		<th>{translations name='eventslist.date'}</th>
		<th>{translations name='eventslist.location'}</th>
		<th>{translations name='eventslist.title'}</th>
	</tr>
	</thead>
	<tbody>
	{foreach $events as $event}
		<tr>
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
	</tbody>
</table>


