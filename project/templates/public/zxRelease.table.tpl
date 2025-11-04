{$prod = $element->getProd()}
<tr class="zxrelease">
	<td class='zxrelease_table_title'>
		<a class='' href='{$element->getUrl()}'>{$element->getTitle()} {if $element->isRealtime()}{assign 'compoTitle' "compo_"|cat:$element->compo}
				<img loading="lazy" src="{$theme->getImageUrl("clock.png")}" title="{translations name="zxPicture.$compoTitle"}" />{/if}
		</a>
	</td>
	<td class='zxrelease_table_year'>
		<a href="{$element->getCatalogueUrl(['years' => $element->getYear()])}">{$element->getYear()}</a>
	</td>
	<td class='zxrelease_table_play zxrelease_play'>
		{if $element->isPlayable() && $element->isDownloadable()}
			{include file=$theme->template('component.play-button.tpl') element=$element}
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
				<img loading="lazy" src="{$theme->getImageUrl("gold_cup.png")}" alt='{translations name='label.firstplace'}'/>{/if}
			{if $element->partyplace=='2'}
				<img loading="lazy" src="{$theme->getImageUrl("silver_cup.png")}" alt='{translations name='label.secondplace'}'/>{/if}
			{if $element->partyplace=='3'}
				<img loading="lazy" src="{$theme->getImageUrl("bronze_cup.png")}" alt='{translations name='label.thirdplace'}'/>{/if}
		{/if}
	</td>
	<td class='zxrelease_table_language'>
		{include file=$theme->template("component.languagelinks.tpl") element=$element}
	</td>
	<td class='zxrelease_table_version'>
		{$element->version}
	</td>
	<td class='zxrelease_table_releasetype'>
		{if $element->releaseType}{translations name="zxRelease.type_{$element->releaseType}"}{/if}
	</td>
	<td class='zxrelease_table_releaseby'>
		{foreach $element->getReleaseBy() as $info}
			<a href="{$info->getUrl()}">{$info->title}</a>{if !$info@last}, {/if}
		{/foreach}
	</td>
	<td class='zxrelease_table_hardware'>
		{include file=$theme->template("component.hardware.tpl") element=$element}
		<div class="zxrelease_table_description">
			{$element->description}
		</div>
	</td>
	<td class="zxrelease_table_format">
		{foreach from=$element->releaseFormat item=format name=rf}
			{if not $smarty.foreach.rf.first}, {/if}
			<a href="{$element->getCatalogueUrlByFiletype($format)}" class="zxrelease-format-link">
				{$element->getFormatEmoji($format)} {translations name="zxRelease.filetype_{$format}"}
			</a>
		{/foreach}
	</td>

	<td class='zxrelease_table_download'>
		<div class="zxrelease_table_download_buttons">
			{if $prod->getLegalStatus() === 'donationware'}
			<a class="button release-sales-button" href="{$prod->externalLink}"
			   target="_blank">{translations name='zxprod.donate'}</a>
			{/if}
			{if $element->isDownloadable()}
				{if $element->fileName}
					<a rel="nofollow" href="{$element->getFileUrl()}"><img loading="lazy" src="{$theme->getImageUrl("disk.png")}" alt="{translations name='label.download'} {$element->getFileName('original', false)}" /></a>
				{/if}
			{elseif $prod->externalLink}
				{if $prod->getLegalStatus() === 'insales'}
					<a class="button release-sales-button" href="{$prod->externalLink}"
					   target="_blank">{translations name='zxprod.purchase'}</a>
				{else}
					<a class="button" href="{$prod->externalLink}"
					   target="_blank">{translations name='zxprod.open_externallink'}</a>
				{/if}
			{/if}
		</div>
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
	<td class='zxrelease_table_links'>
		{include file=$theme->template('component.links.icons.tpl')}
	</td>
</tr>
{$files1List = $element->getImagesList()}
{$filesList = $element->getFilesList('infoFilesSelector')}
{if $filesList || $files1List}
	<tr class="zxrelease-images">
		<td class='zxrelease_table_images' colspan="16">
			{if $files1List}
			    {include file=$theme->template('zxItem.images.tpl') filesList = $files1List preset='prodImage'}
			{/if}
			{if $filesList}
				<div class="zxrelease_table_manuals">
					{include file=$theme->template('zxItem.files.tpl') filesList = $filesList newWindow=true}
				</div>
			{/if}
		</td>
	</tr>
{/if}