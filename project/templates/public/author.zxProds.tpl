{if $prods = $element->getProds()}
	<div class="author_details_section">
		<h1>{translations name="author.prods"}</h1>
		<zx-prods-list element-id="{$element->id}" property="prods"></zx-prods-list>
	</div>
{/if}