{if $prods = $element->getPublisherProds()}
	<div class="group_details_section">
		<h1>{translations name="group.prods_publisher"}</h1>
		<zx-prods-list element-id="{$element->id}" property="publishedProds"></zx-prods-list>
	</div>
{/if}