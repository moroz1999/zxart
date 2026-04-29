<?xml version="1.0" encoding="UTF-8"?>
<structureElements>
{foreach from=$currentElement->getContentList() item=contentItem}
	<structureElement>
		<id><![CDATA[{$contentItem->id}]]></id>
		<structureName><![CDATA[{$contentItem->structureName}]]></structureName>
		<title><![CDATA[{$contentItem->title}]]></title>
		<type><![CDATA[{$contentItem->type}]]></type>
		<dateModified><![CDATA[{$contentItem->dateModified}]]></dateModified>
		<URL><![CDATA[{$contentItem->URL}]]></URL>
	</structureElement>
{/foreach}
</structureElements>