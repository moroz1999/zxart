{if $currentElement->structureType == 'stats'}
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
{/if}
{if $element->title}
	{capture assign="moduleTitle"}
		{$element->title}
	{/capture}
{/if}
{capture assign="moduleContent"}
	<script defer src="{$controller->baseURL}vendor/nnnick/chartjs/dist/Chart.js"></script>
	<div class="stats_section">
		<h2 class="stats_all_years_description">{translations name="stats.all_years"}</h2>
		<canvas class="stats_chart chart_component" data-chartid="{$element->id}_all" width="700" height="200"></canvas>
		<script>
			window.chartsData = window.chartsData || {ldelim}{rdelim};
			window.chartsData["{$element->id}_all"] = {$element->getAllYearsData('zxpicture')};
		</script>
	</div>
	<div class="stats_section">
		<h2 class="stats_rated_years_description">{translations name="stats.rated_years"}</h2>
		<canvas class="stats_chart chart_component" data-chartid="{$element->id}_rated" width="700" height="200"></canvas>
		<script>
			window.chartsData = window.chartsData || {ldelim}{rdelim};
			window.chartsData["{$element->id}_rated"] = {$element->getRatedYearsData('zxpicture')};
		</script>
	</div>
	<div class="stats_section">
		<h2 class="stats_viewshistory_description">{translations name="stats.views"}</h2>
		<canvas class="stats_chart chart_component" data-chartid="{$element->id}_viewshistory" width="700" height="200"></canvas>
		<script>
			window.chartsData = window.chartsData || {ldelim}{rdelim};
			window.chartsData["{$element->id}_viewshistory"] = {$element->getViewsHistoryData()};
		</script>
	</div>
	<div class="stats_section">
		<h2 class="stats_all_years_description">{translations name="stats.all_years_music"}</h2>
		<canvas class="stats_chart chart_component" data-chartid="{$element->id}_all_music" width="700" height="200"></canvas>
		<script>
			window.chartsData = window.chartsData || {ldelim}{rdelim};
			window.chartsData["{$element->id}_all_music"] = {$element->getAllYearsData('zxmusic')};
		</script>
	</div>
	<div class="stats_section">
		<h2 class="stats_rated_years_description">{translations name="stats.rated_years_music"}</h2>
		<canvas class="stats_chart chart_component" data-chartid="{$element->id}_rated_music" width="700" height="200"></canvas>
		<script>
			window.chartsData = window.chartsData || {ldelim}{rdelim};
			window.chartsData["{$element->id}_rated_music"] = {$element->getRatedYearsData('zxmusic')};
		</script>
	</div>
	<div class="stats_section">
		<h2 class="stats_playshistory_description">{translations name="stats.plays"}</h2>
		<canvas class="stats_chart chart_component" data-chartid="{$element->id}_playshistory" width="700" height="200"></canvas>
		<script>
			window.chartsData = window.chartsData || {ldelim}{rdelim};
			window.chartsData["{$element->id}_playshistory"] = {$element->getPlaysHistoryData()};
		</script>
	</div>
	<div class="stats_section">
		<h2 class="stats_viewshistory_description">{translations name="stats.comments"}</h2>
		<canvas class="stats_chart chart_component" data-chartid="{$element->id}_commentshistory" width="700" height="200"></canvas>
		<script>
			window.chartsData = window.chartsData || {ldelim}{rdelim};
			window.chartsData["{$element->id}_commentshistory"] = {$element->getCommentsHistoryData()};
		</script>
	</div>
	<div class="stats_section">
		<h2 class="stats_viewshistory_description">{translations name="stats.uploads"}</h2>
		<canvas class="stats_chart chart_component" data-chartid="{$element->id}_uploadshistory" width="700" height="200"></canvas>
		<script>
			window.chartsData = window.chartsData || {ldelim}{rdelim};
			window.chartsData["{$element->id}_uploadshistory"] = {$element->getUploadsHistoryData()};
		</script>
	</div>
	<div class="stats_section">
		<h2 class="stats_top_graphicsuploaders">{translations name="stats.top_graphicsuploaders"}</h2>
		<table class="table_component">
		{foreach from=$element->getTopWorksUsers('addZxPicture', 10) item=data}
				<tr>
					<td>{$data['count']}</td>
					<td>{$data['user']->userName}</td>
				</tr>
		{/foreach}
		</table>
	</div>
	<div class="stats_section">
		<h2 class="stats_top_musicuploaders">{translations name="stats.top_musicuploaders"}</h2>
		<table class="table_component">
		{foreach from=$element->getTopWorksUsers('addZxMusic', 10) item=data}
				<tr>
					<td>{$data['count']}</td>
					<td>{$data['user']->userName}</td>
				</tr>
		{/foreach}
		</table>
	</div>
	<div class="stats_section">
		<h2 class="stats_top_commentators">{translations name="stats.top_commentators"}</h2>
		<table class="table_component">
		{foreach from=$element->getTopWorksUsers('comment', 30) item=data}
				<tr>
					<td>{$data['count']}</td>
					<td>{$data['user']->userName}</td>
				</tr>
		{/foreach}
		</table>
	</div>
	<div class="stats_section">
		<h2 class="stats_top_voters">{translations name="stats.top_voters"}</h2>
		<table class="table_component">
		{foreach from=$element->getTopVotesUsers(30) item=data}
				<tr>
					<td>{$data['count']}</td>
					<td>{$data['user']->userName}</td>
				</tr>
		{/foreach}
		</table>
	</div>
	<div class="stats_section">
		<h2 class="stats_top_tagadders">{translations name="stats.top_tagadders"}</h2>
		<table class="table_component">
		{foreach from=$element->getTopWorksUsers('tagAdded', 30) item=data}
				<tr>
					<td>{$data['count']}</td>
					<td>{$data['user']->userName}</td>
				</tr>
		{/foreach}
		</table>
	</div>
{/capture}
{assign moduleClass "stats_block"}
{assign moduleTitleClass ""}
{assign moduleContentClass ""}

{include file=$theme->template("component.contentmodule.tpl")}