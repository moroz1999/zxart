<div class="map_footer">
	{if $element->title}<div class="map_footer_title">{$element->title}</div>{/if}
	{if $element->mapCode}
	<div class="map_map map_embedded">
			{$element->mapCode}
		</div>
	{elseif $element->coordinates}
		<script>
			{if !empty($element->styles|trim)}
			window.mapsInfo = window.mapsInfo || {ldelim}{rdelim};
			window.mapsInfo['{$element->id}'] = {ldelim}
				'coordinates': '{$element->coordinates}',
				'title': '{$element->title}',
				'content': '{$element->description}',
				'zoomControlEnabled': true,
				'streetViewControlEnabled': false,
				'mapTypeControlEnabled': false,
				{if $element->styles}'styles': {$element->styles},{/if}
				'heightAdjusted': 'true',
				'height': 0.185
				{rdelim};
			{else}
			window.mapsIframe = window.mapsIframe || {ldelim}{rdelim};
			window.mapsIframe['{$element->id}'] = {$element->getJsonMapIframeInfo()};
			window.mapsIframe['{$element->id}']['height'] = 0.185;
			{/if}
		</script>
	{/if}
</div>