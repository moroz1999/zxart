{if $prods = $element->getProds()}
	<div class="author_details_section">
		<h1>{translations name="author.prods"}</h1>
		<div class="author_prods zxprods_list">
            {foreach $prods as $prod}
                {include file=$theme->template('zxProd.short.tpl') element=$prod}
            {/foreach}
		</div>
	</div>
{/if}