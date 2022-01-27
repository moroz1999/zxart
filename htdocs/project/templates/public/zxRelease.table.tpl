{$prod = $element->getProd()}
<tr class="zxrelease">
	<td class='zxrelease_table_number'>
		{$number}
	</td>

	<td class='zxrelease_table_title'>
		<a class='' href='{$element->getUrl()}'>{$element->getHumanReadableName()} {if $element->isRealtime()}{assign 'compoTitle' "compo_"|cat:$element->compo}
				<img src="{$theme->getImageUrl("clock.png")}" title="{translations name="zxPicture.$compoTitle"}" />{/if}
		</a>
	</td>
	<td class='zxrelease_table_year'>
		{$element->getYear()}
	</td>
	<td class='zxrelease_table_play zxrelease_play'>
		{if $element->isPlayable() && $element->isDownloadable()}
			<button class="button" onclick="emulatorComponent.start('{$element->getFileUrl('play')}')">{translations name="zxrelease.play"}</button>
		{/if}
	</td>
	<td class='zxrelease_table_source'>
		{$partyElement = $element->getPartyElement()}
		{if $partyElement}
			<a href='{$partyElement->URL}'>{if $partyElement->abbreviation}{$partyElement->abbreviation}{else}{$partyElement->title}{/if}</a>
		{/if}
	</td>
	<td class='zxrelease_table_partyplace'>
		{if $partyElement}
			{if $element->partyplace!='0'}{$element->partyplace}{/if}
			{if $element->partyplace=='1'}
				<img src="{$theme->getImageUrl("gold_cup.png")}" alt='{translations name='label.firstplace'}'/>{/if}
			{if $element->partyplace=='2'}
				<img src="{$theme->getImageUrl("silver_cup.png")}" alt='{translations name='label.secondplace'}'/>{/if}
			{if $element->partyplace=='3'}
				<img src="{$theme->getImageUrl("bronze_cup.png")}" alt='{translations name='label.thirdplace'}'/>{/if}
		{/if}
	</td>
	<td class='zxrelease_table_format'>
		{foreach $element->releaseFormat as $format}{translations name="zxRelease.filetype_$format"} {/foreach}
	</td>
	<td class='zxrelease_table_language'>
		{$element->getSupportedLanguageString()}
	</td>
	<td class='zxrelease_table_version'>
		{$element->version}
	</td>
	<td class='zxrelease_table_releasetype'>
		{if $element->releaseType}{translations name="zxRelease.type_{$element->releaseType}"}{/if}
	</td>
	<td class='zxrelease_table_releaseby'>
		{if $authors=$element->getAuthorsInfo('release', ['release'])}
			{foreach $authors as $info}
				<a href="{$info.authorElement->getUrl()}">{$info.authorElement->title}</a>{if !$info@last}, {/if}
			{/foreach}
		{/if}
		{if $publishers = $element->publishers}
			{foreach from=$publishers item=publisher}
				<a href="{$publisher->getUrl()}">{$publisher->title}</a>{if !$publisher@last}, {/if}
			{/foreach}
		{/if}
	</td>
	<td class='zxrelease_table_hardware'>
		{foreach $element->hardwareRequired as $hardware}
			<div class="zxrelease_table_hardware_item">{translations name="hardware.item_{$hardware}"}</div>
		{/foreach}
	</td>
	<td class='zxrelease_table_links'>
		{include file=$theme->template('component.links.icons.tpl')}
	</td>
	<td class='zxrelease_table_download'>
		{if $element->isDownloadable()}
			{if $element->fileName}
				<a rel="nofollow" href="{$element->getFileUrl()}"><img src="{$theme->getImageUrl("disk.png")}" alt="{translations name='label.download'} {$element->getFileName('original', false)}" /></a>
			{/if}
		{elseif $prod->externalLink}
			<a class="button" href="{$prod->externalLink}"
			   target="_blank">{translations name='zxprod.externallink'}</a>
		{/if}
	</td>
	<td class='zxrelease_table_downloaded'>
		{if $element->isDownloadable()}
			{$element->downloads}
		{/if}
	</td>
	<td class='zxrelease_table_plays'>
		{if $element->isDownloadable()}
			{$element->plays}
		{/if}
	</td>
</tr>
{$files1List = $element->getImagesList()}
{$files2List = $element->getFilesList('screenshotsSelector')}
{if $files1List  || $files2List}
	<tr class="zxrelease">
		<td class='zxrelease_table_images' colspan="15">
			{include file=$theme->template('zxItem.images.tpl') filesList = $files2List preset='prodImage'}
			{include file=$theme->template('zxItem.images.tpl') filesList = $files1List preset='prodImage'}
		</td>
	</tr>
{/if}