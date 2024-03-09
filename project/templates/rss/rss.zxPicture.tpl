<item>
	<title><![CDATA[{$element->title}]]></title>
	<link>{$element->URL}</link>
	<description><![CDATA[
	<a href="{$element->getUrl()}"><img style="border:none" src="{$element->getImageUrl()}" alt="{$element->title}"/></a>
	<div><a href='{$element->getUrl()}'>{$element->title}</a> by
	{foreach from=$element->getAuthorsList() item=author name=authors}
		<a href='{$author->getUrl()}'>{$author->title}</a>{if !$smarty.foreach.authors.last}, {/if}
	{/foreach}
	</div>
	]]></description>
	<pubDate>{$element->rssDate}</pubDate>
	<guid>{$element->guid}</guid>
</item>