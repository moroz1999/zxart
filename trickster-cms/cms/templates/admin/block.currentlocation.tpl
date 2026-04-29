<div class='current_location'>
	<div class="current_location_linktree">
		<a class="current_location_item" href="{$controller->baseURL}admin" target="_blank">{$controller->domainName}</a>
		{foreach from=$currentLocation item=element name=currentLocation}
			<span class="location_separator">/</span>
			<a href="{$element.URL}" class="current_location_item">{$element.title}</a>
			{if !$smarty.foreach.currentLocation.last}
				<span class='linktree_arrow'></span>
			{/if}
		{/foreach}
	</div>
</div>