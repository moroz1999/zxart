{if ($element->getYearsWorks('authorPicture'))}
	<div class="author_details_section">
		<h1>{translations name="author.graphics"}</h1>
		<a class="author_details_save button" href="{$element->getSaveUrl('zxPicture')}">{translations name='author.save'}</a>
		<div class='author_pictures gallery_pictures' id="gallery_{$element->id}">
			{foreach from=$element->getYearsWorks('authorPicture') key=year item=pictures}
				<div class='author_details_year'>
					<h2 class="author_details_year_title">{if $year=='0'}{translations name='label.unknownyear'}{else}{$year}{/if}</h2>
					{include file=$theme->template('component.pictureslist.tpl') pictures=$pictures}
				</div>
			{/foreach}
		</div>
		{if $chartData = $element->getChartData('authorPicture')}
			<script defer src="{$controller->baseURL}vendor/nnnick/chartjs/dist/Chart.js"></script>
			<div class="author_details_stats">
				<h3 class="author_details_views_title">{translations name="author.views_chart"}</h3>
				<canvas class="author_details_views_chart chart_component" data-chartid="{$element->id}_views" width="700" height="200"></canvas>
			</div>
			<script>
				window.chartsData = window.chartsData || {ldelim}{rdelim};
				window.chartsData["{$element->id}_views"] = {$chartData};
			</script>
		{/if}
	</div>
{/if}