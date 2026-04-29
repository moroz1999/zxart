<?xml version="1.0" encoding="UTF-8"?>
<structureElements>
{foreach from=$exportData item=exportElement}
	{include file="xml.elementData.tpl" elementData=$exportElement}
{/foreach}
</structureElements>