{if ($element->getYearsWorks('authorMusic'))}
	<div class="author_details_section">
		<h1>{translations name="author.music"}</h1>
		<a class="author_details_save button" href="{$element->getSaveUrl('zxMusic')}">{translations name='author.save_music'}</a>
		<div class='author_musics gallery_musics'>
			{foreach from=$element->getYearsWorks('authorMusic') key=year item=tunes}
				<div class='author_details_year'>
					<h2 class="author_details_year_title">{if $year=='0'}{translations name='label.unknownyear'}{else}{$year}{/if}</h2>
					{include file=$theme->template("component.musictable.tpl") musicList=$tunes element=$element showAuthors=false showYear=false}
				</div>
			{/foreach}
		</div>
		{if $chartData = $element->getChartData('authorMusic')}
			<script defer src="{$controller->baseURL}vendor/nnnick/chartjs/dist/Chart.js"></script>
			<div class="author_details_stats">
				<h3 class="author_details_plays_title">{translations name="author.plays_chart"}</h3>
				<canvas class="author_details_plays_chart chart_component" data-chartid="{$element->id}_plays"></canvas>
			</div>
			<script>
				window.chartsData = window.chartsData || {ldelim}{rdelim};
				window.chartsData["{$element->id}_plays"] = {$chartData};
			</script>
		{/if}
	</div>
{/if}
