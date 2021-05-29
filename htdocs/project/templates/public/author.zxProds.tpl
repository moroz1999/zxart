{if $prods = $element->getProds()}
	<div class="author_details_section">
		<h1>{translations name="author.prods"}</h1>
		<script>
			window.elementsData = window.elementsData ? window.elementsData : { };
			window.elementsData[{$element->id}] = {$element->getJsonInfo('zxProdsList')};
		</script>
		<app-zx-prods-list element-id="{$element->id}"></app-zx-prods-list>
	</div>
{/if}