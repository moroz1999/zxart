{if $releases = $element->getReleases()}
	<div class="author_details_section">
		<h1>{translations name="author.releases"}</h1>
		<zx-prods-list element-id="{$element->id}" property="releases" layout="years"></zx-prods-list>
	</div>
{/if}
