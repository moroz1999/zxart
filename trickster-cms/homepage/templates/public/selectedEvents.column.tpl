{assign var='events' value=$element->getEventsElements()}
{if $events}
	{capture assign="moduleContent"}
		{if $element->title}
			{capture assign="moduleTitle"}
				{$element->title}
			{/capture}
		{/if}

		{foreach $events as $event}
			<div class="column_selectedevents_item">
				{include file=$theme->template("event.column.tpl") element=$event}
			</div>
		{/foreach}
	{/capture}

	{assign moduleClass "column_selectedevents"}
	{assign moduleContentClass "column_selectedevents_content"}
	{assign moduleTitleClass "column_selectedevents_title"}

	{include file=$theme->template("component.columnmodule.tpl")}
{/if}