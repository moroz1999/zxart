{if $file = $element->getCurrentReleaseFile()}
	{if $element->title}{$element->title}{/if}: {$file->getItemName()}

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
					{$file->getSize()}
				</td>
			</tr>
			<tr>
				<td class='info_table_label'>
					{translations name='zxrelease.file_md5'}:
				</td>
				<td class='info_table_value'>
					{$file->getMd5()}
				</td>
			</tr>
		</table>
		<pre>{$element->getCurrentReleaseContentFormatted()}</pre>
{/if}