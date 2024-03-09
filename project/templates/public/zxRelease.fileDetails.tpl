{if $file = $element->getCurrentReleaseFileInfo()}
	{capture assign="moduleTitle"}{if $element->title}{$element->title}{/if}: {$file['fileName']}{/capture}
	{capture assign="moduleContent"}
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
	{/capture}

	{assign moduleClass "zxrelease_details"}
	{assign moduleAttributes ""}
	{assign moduleTitleClass ""}
	{assign moduleContentClass ""}

	{include file=$theme->template("component.contentmodule.tpl")}
{/if}