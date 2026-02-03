{if $prods = $element->getGroupProds()}
	<div class="group_details_section">
		<h1>{translations name="group.prods_producers"}</h1>
		<script>
			window.elementsData = window.elementsData ? window.elementsData : { };
			window.elementsData[{$element->id}] = {$element->getJsonInfo('zxProdsList')};
		</script>
		<zx-prods-list element-id="{$element->id}"></zx-prods-list>
	</div>
{/if}
