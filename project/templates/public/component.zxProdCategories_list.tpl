{if $categories =$element->getCategories()}
	<ul class="zxprodcategories_list">
		{foreach $categories as $category}
			{include file=$theme->template($category->getTemplate()) element=$category}
		{/foreach}
	</ul>
{/if}