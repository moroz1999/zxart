{if $element->enableFilter}
	{assign "monthsInfo" $element->getMonthsInfo()}
	{if $monthsInfo && count($monthsInfo)>1 || $element->getSelectedMonthStamp()}
		<div class="eventslist_filter">
			<select class="dropdown_placeholder eventslist_filter_select" autocomplete="off">
				<option value="none">
					{translations name='events.monthfiltertitle'}
				</option>
				{foreach $monthsInfo as $monthInfo}
					<option value='{$monthInfo.stamp}'
							{if $monthInfo.stamp == $element->getSelectedMonthStamp()}selected='selected'{/if}>
						{translations name='calendar.month_'|cat:$monthInfo.month} {$monthInfo.year}
					</option>
				{/foreach}
			</select>
		</div>
	{/if}
{/if}
