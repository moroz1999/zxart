<div class="zxpicture_short">
	{stripdomspaces}
	<a href="{$element->getUrl()}" class="zxpicture_short_image_link" onclick="return false;">
		<img id='image_{$element->id}' class='zxpicture_short_image zxgallery_item{if $currentMode.mode != 'mix' && $element->isFlickering()} flicker_image{/if}' src='{$element->getImageUrl()}' alt='{$element->title} ({$element->title})'/>
	</a>
	<div class="zxpicture_short_bottom">
		<div class="zxpicture_short_title"><a class='zxpicture_short_title_link' href='{$element->getUrl()}'>{$element->title} {if $element->isRealtime()}{assign 'compoTitle' "compo_"|cat:$element->compo}<img src="{$theme->getImageUrl("clock.png")}" title="{translations name="zxPicture.$compoTitle"}" />{/if}</a> - {foreach from=$element->getAuthorsList() item=author name=authors}<a class='zxpicture_short_title_author' href="{$author->getUrl()}">{$author->title}</a>{if !$smarty.foreach.authors.last} &amp; {/if}{/foreach}</div>
        {if $element->getPartyElement()}
			<div class='zxpicture_short_party'>
				{if $element->partyplace=='1'}<img src="{$theme->getImageUrl("gold_cup.png")}" alt='{translations name='label.firstplace'}'/>{/if}
				{if $element->partyplace=='2'}<img src="{$theme->getImageUrl("silver_cup.png")}" alt='{translations name='label.secondplace'}'/>{/if}
				{if $element->partyplace=='3'}<img src="{$theme->getImageUrl("bronze_cup.png")}" alt='{translations name='label.thirdplace'}'/>{/if}
				<a href='{$element->getPartyElement()->URL}'>{$element->getPartyElement()->title}</a>{if !empty($element->partyplace)} ({$element->partyplace}){/if}
			</div>
        {/if}
		<div class="zxpicture_short_controls">
			{include file=$theme->template("component.votecontrols.tpl") element=$element}
			{include file=$theme->template("component.playlist.tpl") element=$element}
		</div>
		{if $element->year}
		<div class="zxpicture_short_year">
			{$element->year}
		</div>
		{/if}
	</div>
	{/stripdomspaces}
	<script>
		if (!window.picturesList) window.picturesList = [];
		window.picturesList.push({$element->getJsonInfo()});
	</script>

	<script type='text/javascript'>
		if (!window.galleryPictures) window.galleryPictures = [];
		window.galleryPictures.push({$element->id});

		if (!window.imageInfoIndex) window.imageInfoIndex = {ldelim}{rdelim};
		window.imageInfoIndex['{$element->id}'] = {ldelim}
			'smallImage': "{$element->getImageUrl(1, false,false)}",
			'largeImage': "{$element->getImageUrl(2)}",
			'detailsURL': '{$element->URL}',
			'title': "{$element->title|escape:'javascript'}",
			'id': '{$element->id}',
			'flickering': '{$element->isFlickering() && ($currentMode.mode!='mix')}'
			{rdelim};
	</script>
</div>