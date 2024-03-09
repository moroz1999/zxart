{if $prods = $element->getProds()}
	{include file=$theme->template('component.heading.tpl') value={translations name="author.prods"}}
	{foreach $prods as $prod}
		{include file=$theme->template('zxProd.short.tpl') element=$prod}
	{/foreach}
{/if}