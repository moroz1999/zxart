<script>

	window.baseURL = '{$controller->baseURL}';
	window.rootURL = '{$controller->rootURL}';
	{if $currentElement}
		window.currentElementURL = '{$currentElement->URL}';
		window.currentElementId = '{$currentElement->id}';
		window.currentAction = '{$currentElement->actionName}';
	{/if}

</script>
<script>
		window.translations= {$translationsList|json_encode};
</script>