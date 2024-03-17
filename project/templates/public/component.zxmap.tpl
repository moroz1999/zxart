<link rel="stylesheet" href="{$controller->baseURL}js/leaflet/leaflet.css"/>
<script src="{$controller->baseURL}js/leaflet/leaflet.js"></script>
<link rel="stylesheet" href="{$controller->baseURL}js/leaflet/MarkerCluster.css"/>
<link rel="stylesheet" href="{$controller->baseURL}js/leaflet/MarkerCluster.Default.css"/>
<script src="{$controller->baseURL}js/leaflet/leaflet.markercluster.js"></script>
<div class="zxmap" data-id="{$element->id}"></div>
{if !empty($locations)}
    <div class="zxmap_locations">
        {foreach $locations as $location}
            <div class="zxmap_location">
                <a href="{$location->getUrl()}">{$location->title}</a> ({$location->getAmountInLocation()})
            </div>
        {/foreach}
    </div>
{/if}
<script>
    window.mapsData = window.mapsData || {ldelim}{rdelim};
    window.mapsData["{$element->id}"] = {$element->getMapData()};
</script>