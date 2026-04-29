{foreach $events as $event}
	{include file=$theme->template($event->getTemplate('detailed')) element=$event}
{/foreach}





