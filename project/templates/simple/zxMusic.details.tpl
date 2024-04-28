{if $element->title}{include file=$theme->template('component.heading.tpl') value=$element->title}{/if}
	{if $element->isPlayable()}
		<div class="music_details_controls">
			<div class="music_controls_short elementid_{$element->id}"></div><span class="music_controls_label">{translations name="zxmusic.play"}</span>
		</div>
		<script>
			if (!window.musicList) window.musicList = [];
			window.musicList.push({$element->getJsonInfo()});
		</script>
	{/if}
	{if $element->embedCode}
		<div class="music_details_embed">
			{$element->embedCode}
		</div>
	{/if}

	{include file=$theme->template("component.musicinfo.tpl")}
	<div class="music_editing_controls editing_controls">
		{if isset($currentElementPrivileges.showPublicForm) && $currentElementPrivileges.showPublicForm}
			<a class="button" href="{$element->URL}id:{$element->id}/action:showPublicForm/">{translations name='zxmusic.edit'}</a>
		{/if}
		{if isset($currentElementPrivileges.publicDelete) && $currentElementPrivileges.publicDelete}
			<a class="button delete_button" href="{$element->URL}id:{$element->id}/action:publicDelete/">{translations name='zxmusic.delete'}</a>
		{/if}
	</div>

	{if isset($currentElementPrivileges.submitTags) && $currentElementPrivileges.submitTags == true}
		{include file=$theme->template("tags.form.tpl") element=$element}
	{/if}
	{if $element->denyPlaying}<p>{translations name="zxitem.playingdenied"}</p>{/if}
	{include file=$theme->template('component.comments.tpl')}
	{if $element->denyComments}<p>{translations name="zxitem.commentsdenied"}</p>{/if}

	{include file=$theme->template('component.voteslist.tpl')}
	{if $element->denyVoting}<p>{translations name="zxitem.votingdenied"}</p>{/if}
	{if $element->getChartData()}
		<script defer src="{$controller->baseURL}js/Chart.min.js"></script>
		<div class="music_details_stats">
			<h2 class="music_details_plays_title">{translations name="zxmusic.plays_chart"}</h2>
			<canvas class="music_details_plays_chart chart_component" data-chartid="{$element->id}_plays" width="700" height="200"></canvas>
		</div>
		<script>
			window.chartsData = window.chartsData || {ldelim}{rdelim};
			window.chartsData["{$element->id}_plays"] = {$element->getChartData()};
		</script>
	{/if}
