{if $element->title}
	{capture assign="moduleTitle"}
		{$element->title}
	{/capture}
{/if}
{assign var="contentClass" value=""}
{if !empty($element->content)}{$contentClass = $contentClass|cat:" content_text"}{/if}
{if !empty($element->getFullAddress())}{$contentClass = $contentClass|cat:" content_map_address"}{/if}
{if !empty($element->mapCode) || !empty($element->coordinates)}{$contentClass = $contentClass|cat:" content_map_map"}{/if}
{capture assign="moduleContent"}
	<div class='map_content'>
		<div class='map_content_text html_content'>
			{$element->content}

		{if $element->getFullAddress()}
			<p class="map_address">
				{$element->getFullAddress()}
			</p>
		{/if}
	    </div>
	</div>
	<div class="map_map googlemap_id_{$element->id}">
		<script>
			window.mapsInfo = window.mapsInfo || {ldelim}{rdelim};
			window.mapsInfo['{$element->id}'] = {$element->getJsonMapInfo()};
		</script>
	</div>
{/capture}

{assign moduleClass "map map_details map_id_{$element->id}"}
{assign moduleTitleClass "map_title"}
{include file=$theme->template("component.contentmodule.tpl") moduleContentClass=$contentClass}
