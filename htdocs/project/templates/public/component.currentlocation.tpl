{if isset($currentLocation) && count($currentLocation)>1}
	<div class='current_location_block'>
	{foreach from=$currentLocation item=currentLocationElement name=currentLocation}
		{if !$smarty.foreach.currentLocation.last}
			<a href='{$currentLocationElement->URL}'>{$currentLocationElement->title}</a> <span class='current_location_delimiter'>></span>
		{else}
			{$currentLocationElement->getHumanReadableName()}
		{/if}
	{/foreach}
	</div>
{/if}