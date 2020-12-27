{foreach $structure as $item}
	<tr>
		<td style="padding-left: {$level}em;">
			{if $level==0 || $item.type !== 'file'}
				{$item.fileName}
			{else}
				{if $item.viewable}
					<a href="{$element->URL}action:viewFile/id:{$element->id}/fileId:{$item.id}/">{$item.fileName}</a>
				{else}
					{$item.fileName}
				{/if}

			{/if}
		</td>
		<td>{$item.size}</td>
		<td>{translations name="zxrelease.filetype_{$item.type}"}</td>
		<td>
			{if $item.type!='folder'}
				<a href="{$controller->baseURL}zxfile/id:{$element->id}/fileId:{$item.id}/{$item.fileName}">{translations name="zxRelease.download"}</a>
			{/if}
		</td>
		<td>
			{if $item.type=='file' && $item.viewable === true}
				<a href="{$element->URL}action:viewFile/id:{$element->id}/fileId:{$item.id}/">{translations name="zxRelease.view"}</a>
			{/if}
		</td>
	</tr>
	{if isset($item['items'])}
		{include $theme->template('zxRelease.structure.tpl') structure=$item['items'] level=$level+1}
	{/if}
{/foreach}