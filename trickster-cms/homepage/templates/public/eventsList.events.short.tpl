{foreach $events as $event}
	{include file=$theme->template($event->getTemplate('short')) element=$event}
{/foreach}
