{if $prods = $element->getGroupProds()}
	<div class="group_details_section">
		<h1>{translations name="group.prods_producers"}</h1>
		<zx-prods-list element-id="{$element->id}" property="prods"></zx-prods-list>
	</div>
{/if}
