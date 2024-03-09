{if $prods = $element->getGroupProds()}
	<div class="group_details_section">
		<h1>{translations name="group.prods_producers"}</h1>
		<app-zx-prods-list element-id="{$element->id}" property="prods"></app-zx-prods-list>
	</div>
{/if}
