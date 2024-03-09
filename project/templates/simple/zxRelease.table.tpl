{$number}.
<a class='' href='{$element->getUrl()}'>{$element->getTitle()}</a> {$element->getYear()} {foreach $element->releaseFormat as $format}{translations name="zxRelease.filetype_$format"} {/foreach}<br>
{if $partyElement = $element->getPartyElement()}<a href='{$partyElement->URL}'>{$partyElement->title}</a>{/if}{if $element->partyplace>0}({$element->partyplace}){/if}
{$element->getSupportedLanguageString()} {$element->version} {if $element->releaseType}{translations name="zxRelease.type_{$element->releaseType}"}{/if} <br>
{if $authors=$element->getAuthorsInfo('release', ['release'])}
	{foreach $authors as $info}
		<a href="{$info.authorElement->getUrl()}">{$info.authorElement->title}</a>{if !$info@last}, {/if}
	{/foreach}
{elseif $publishers = $element->publishers}
	{foreach from=$publishers item=publisher}
		<a href="{$publisher->getUrl()}">{$publisher->title}</a>{if !$publisher@last}, {/if}
	{/foreach}
{/if}
{if $element->isDownloadable()}
	{if $element->fileName}
		<a href="{$element->getFileUrl()}">{translations name='label.download'} {$element->getFileName('original', false)}</a>
	{/if}
{/if}
<br>
{$files1List = $element->getImagesList()}
{$files2List = $element->getFilesList('screenshotsSelector')}
{if $files1List  || $files2List}
<tr class="zxrelease">
	<td class='zxrelease_table_images' colspan="16">
		{include file=$theme->template('zxItem.images.tpl') filesList = $files2List preset='prodImage'}
		{include file=$theme->template('zxItem.images.tpl') filesList = $files1List preset='prodImage'}
	</td>
</tr>
{/if}
<br>