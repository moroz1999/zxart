<script>
	/*<![CDATA[*/
	{if isset($playlistsElement)}
	window.playlistsElementUrl = '{$playlistsElement->URL}';
	window.playlists = {$playlistsElement->getJson()};
	{/if}
	/*]]>*/
</script>