{if $prods = $element->getPublisherProds()}
	<div class="group_details_section">
		<h1>{translations name="group.prods_publisher"}</h1>
		<div class="group_prods zxprods_list">
			{foreach $prods as $prod}
				{include file=$theme->template('zxProd.short.tpl') element=$prod}
			{/foreach}
		</div>
	</div>
{/if}
