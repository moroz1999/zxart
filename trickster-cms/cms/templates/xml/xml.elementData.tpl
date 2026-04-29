<structureElement>
	<structureData>
	{foreach from=$elementData.structureData key=fieldName item=value}
		<field name='{$fieldName}'><![CDATA[{$value}]]></field>
	{/foreach}
	</structureData>
	<moduleData>
	{foreach from=$elementData.moduleData key=languageId item=moduleData}
		<language id='{$languagesList[$languageId]}'>
			{foreach from=$moduleData key=fieldName item=value}
				<field name='{$fieldName}'><![CDATA[{$value}]]></field>
			{/foreach}
		</language>
	{/foreach}
	</moduleData>
	<childrenData>
	{foreach from=$elementData.childrenData item=childData}
		{include file="xml.elementData.tpl" elementData=$childData}
	{/foreach}
	</childrenData>
</structureElement>