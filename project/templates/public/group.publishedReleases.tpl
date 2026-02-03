{if $element->publishedReleases}
	<div class="group_details_section">
		<h1>{translations name="group.publishedReleases"}</h1>
		<zx-prods-list element-id="{$element->id}" property="releases" layout="years"></zx-prods-list>
	</div>
{/if}
