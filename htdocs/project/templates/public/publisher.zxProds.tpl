{if $prods = $element->getPublisherProds()}
	<div class="group_details_section">
		<h1>{translations name="group.prods_publisher"}</h1>
		<script>
			window.elementsData = window.elementsData ? window.elementsData : { };
			window.elementsData[{$element->id}] = {$element->getPublisherProdsJson()};
		</script>
		<app-zx-prods-list element-id="{$element->id}"></app-zx-prods-list>

	</div>
{/if}
