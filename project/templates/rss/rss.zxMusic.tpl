<item>
	<title><![CDATA[{$element->title}]]></title>
	<link>{$element->URL}</link>
	<description><![CDATA[
	<div><a href='{$element->getUrl()}'>{$element->title}</a> by
	{foreach from=$element->getAuthorsList() item=author name=authors}
		<a href='{$author->getUrl()}'>{$author->title}</a>{if !$smarty.foreach.authors.last}, {/if}
	{/foreach}
	</div>

	{if $element->getMp3FilePath()}
	<audio controls>
	   <source src="{$element->getMp3FilePath()}" type="audio/mpeg">
	</audio>
	{/if}
	]]></description>
	<pubDate>{$element->rssDate}</pubDate>
	<guid>{$element->guid}</guid>
</item>