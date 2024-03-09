{if $prods = $element->getProds()}
	<div class="author_details_section">
		<h1>{translations name="author.prods"}</h1>
		<app-zx-prods-list element-id="{$element->id}" property="prods"></app-zx-prods-list>
	</div>
{/if}