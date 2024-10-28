{if $file = $element->getCurrentReleaseFileInfo()}
	{if $element->title}{$element->title}{/if}: {$file['fileName']}

		<table class='zxrelease_details_info info_table'>
			<tr>
				<td class='info_table_label'>
					{translations name='zxrelease.file_release'}:
				</td>
				<td class='info_table_value'>
					<a href="{$element->getUrl()}">{$element->title}</a>
				</td>
			</tr>
			<tr>
				<td class='info_table_label'>
					{translations name='zxrelease.file_size'}:
				</td>
				<td class='info_table_value'>
					{$file['size']}
				</td>
			</tr>
			<tr>
				<td class='info_table_label'>
					{translations name='zxrelease.file_md5'}:
				</td>
				<td class='info_table_value'>
					{$file['md5']}
				</td>
			</tr>
		</table>
		<pre>{$element->getCurrentReleaseContentFormatted()}</pre>
{/if}