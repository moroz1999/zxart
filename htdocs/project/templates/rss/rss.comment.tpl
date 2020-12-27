<item>
	<title><![CDATA[{$element->getParentElement()->title} ({$element->getUser()->userName})]]></title>
	<link>{$element->getParentElement()->getUrl()}</link>
	<description><![CDATA[
		<table>
			<tr>
				<td>{*
					<a href="{$element->getParentElement()->getUrl()}" style="display: block;">
						<img style="display: block; border:none" src="{$element->getParentElement->getImageUrl(0)}" alt="{$element->getParentElement()->title}"/>
					</a>
				</td>*}
				<td>
					<div style="font-weight: bold">{$element->getUser()->userName}:</div>
					<div>{$element->content}</div>
				</td>
			</tr>
		</table>
		]]>
	</description>
	<pubDate>{$element->rssDate}</pubDate>
	<guid>{$element->guid}</guid>
</item>