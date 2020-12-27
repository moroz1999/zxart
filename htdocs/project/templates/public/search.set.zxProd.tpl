<h2 class='search_results_group_title'>{translations name=$translationCode}</h2>
<div class="search_results_group_zxprods zxprods_list">
	{foreach $set->elements as $prod}
		{include file=$theme->template('zxProd.short.tpl') element=$prod}
	{/foreach}
</div>
