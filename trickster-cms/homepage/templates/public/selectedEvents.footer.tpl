{assign var='events' value=$element->getEventsElements()}

{if $events}
	<div class="footer_selectedevents">
		{foreach $events as $event}
				{include file=$theme->template("event.short.tpl") element=$event}
		{/foreach}
	</div>
{/if}