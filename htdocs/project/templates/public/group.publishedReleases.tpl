{if $element->publishedReleases}
	<div class="group_details_section">
		<h1>{translations name="group.publishedReleases"}</h1>
		<div class="group_prods zxreleases_list">
			{foreach $element->publishedReleases as $release}
				{include file=$theme->template($release->getTemplate()) element=$release}
			{/foreach}
		</div>
	</div>
{/if}
