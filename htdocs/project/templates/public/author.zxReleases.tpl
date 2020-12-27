{if $releases = $element->getReleases()}
	<div class="author_details_section">
		<h1>{translations name="author.releases"}</h1>
		<div class="author_releases zxreleases_list">
            {foreach $releases as $release}
                {include file=$theme->template('zxRelease.short.tpl') element=$release}
            {/foreach}
		</div>
	</div>
{/if}