{*this subtemplate is used by eventsList an selectedEvents*}
{$listLayout = $element->getCurrentLayout('listLayout')}
{$currentLayout = $element->getCurrentLayout()}
{if $currentLayout == 'listaggregated'}
    {$groupedEventsIndex = $element->getGroupedEvents('month')}
{elseif $currentLayout == 'calendar'}
    {$groupedEventsIndex = $element->getGroupedEvents('list')}
{else}
    {$groupedEventsIndex = $element->getGroupedEvents($listLayout)}
{/if}
{$monthsInfo = $element->getMonthsInfo()}
{include file=$theme->template('eventsList.filter.tpl')}
{if $groupedEventsIndex}
    {if $currentLayout == 'calendar'}
        {include file=$theme->template("eventsList.$currentLayout.tpl")}
    {else}
        {if $listLayout == 'month'}
            <div class="eventlist_months">
                {foreach $groupedEventsIndex as $groupedEvents}
                    {$monthInfo = $monthsInfo.{$groupedEvents@key}}
                    <div class="eventslist_month">
                        <h2 class="eventslist_month_title">
                            {translations name='calendar.month_'|cat:$monthInfo.month} {$monthInfo.year}
                        </h2>
                        <div class="eventslist_month_events eventlist_list">
                            {include file=$theme->template("eventsList.events.$currentLayout.tpl") dateInfo=$monthInfo events=$groupedEvents}
                        </div>
                    </div>
                {/foreach}
            </div>
        {elseif $listLayout == 'list'}
            <div class="eventlist_list">
                {include file=$theme->template("eventsList.events.$currentLayout.tpl") dateInfo=$monthsInfo events=$groupedEventsIndex list=true}
            </div>
        {/if}
    {/if}
{/if}