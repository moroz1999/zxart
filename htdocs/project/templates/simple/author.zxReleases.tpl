{if $releases = $element->getReleases()}
{include file=$theme->template('component.heading.tpl') value={translations name="author.releases"}}
{foreach $releases as $release}
	{include file=$theme->template('zxRelease.short.tpl') element=$release}
{/foreach}
{/if}