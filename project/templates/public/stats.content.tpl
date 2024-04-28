{if $currentElement->structureType == 'stats'}
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
{/if}
{if $element->title}
    {capture assign="moduleTitle"}
        {$element->title}
    {/capture}
{/if}
{capture assign="moduleContent"}
    <script defer src="{$controller->baseURL}js/Chart.min.js"></script>
    <div class="stats_section_graphs">
        <div class="stats_section">
            <h2 class="stats_all_years_description">{translations name="stats.all_years_prods"}</h2>
            <canvas class="stats_chart chart_component" data-chartid="{$element->id}_all_prods" width="900" height="200"></canvas>
            <script>
                window.chartsData = window.chartsData || {ldelim}{rdelim};
                window.chartsData["{$element->id}_all_prods"] = {$element->getAllYearsData('zxprod')};
            </script>
        </div>
        <div class="stats_section">
            <h2 class="stats_rated_years_description">{translations name="stats.rated_years_prods"}</h2>
            <canvas class="stats_chart chart_component" data-chartid="{$element->id}_rated_prods" width="900"
                    height="200"></canvas>
            <script>
                window.chartsData = window.chartsData || {ldelim}{rdelim};
                window.chartsData["{$element->id}_rated_prods"] = {$element->getRatedYearsData('zxprod')};
            </script>
        </div>
{*        <div class="stats_section">*}
{*            <h2 class="stats_viewshistory_description">{translations name="stats.views"}</h2>*}
{*            <canvas class="stats_chart chart_component" data-chartid="{$element->id}_runshistory_prods" width="900"*}
{*                    height="200"></canvas>*}
{*            <script>*}
{*                window.chartsData = window.chartsData || {ldelim}{rdelim};*}
{*                window.chartsData["{$element->id}_runshistory"] = {$element->getRunsHistoryData()};*}
{*            </script>*}
{*        </div>*}

        <div class="stats_section">
            <h2 class="stats_all_years_description">{translations name="stats.all_years"}</h2>
            <canvas class="stats_chart chart_component" data-chartid="{$element->id}_all" width="900"
                    height="200"></canvas>
            <script>
                window.chartsData = window.chartsData || {ldelim}{rdelim};
                window.chartsData["{$element->id}_all"] = {$element->getAllYearsData('zxpicture')};
            </script>
        </div>
        <div class="stats_section">
            <h2 class="stats_rated_years_description">{translations name="stats.rated_years"}</h2>
            <canvas class="stats_chart chart_component" data-chartid="{$element->id}_rated" width="900"
                    height="200"></canvas>
            <script>
                window.chartsData = window.chartsData || {ldelim}{rdelim};
                window.chartsData["{$element->id}_rated"] = {$element->getRatedYearsData('zxpicture')};
            </script>
        </div>
        <div class="stats_section">
            <h2 class="stats_viewshistory_description">{translations name="stats.views"}</h2>
            <canvas class="stats_chart chart_component" data-chartid="{$element->id}_viewshistory" width="900"
                    height="200"></canvas>
            <script>
                window.chartsData = window.chartsData || {ldelim}{rdelim};
                window.chartsData["{$element->id}_viewshistory"] = {$element->getViewsHistoryData()};
            </script>
        </div>

        <div class="stats_section">
            <h2 class="stats_all_years_description">{translations name="stats.all_years_music"}</h2>
            <canvas class="stats_chart chart_component" data-chartid="{$element->id}_all_music" width="900" height="200"></canvas>
            <script>
                window.chartsData = window.chartsData || {ldelim}{rdelim};
                window.chartsData["{$element->id}_all_music"] = {$element->getAllYearsData('zxmusic')};
            </script>
        </div>
        <div class="stats_section">
            <h2 class="stats_rated_years_description">{translations name="stats.rated_years_music"}</h2>
            <canvas class="stats_chart chart_component" data-chartid="{$element->id}_rated_music" width="900"
                    height="200"></canvas>
            <script>
                window.chartsData = window.chartsData || {ldelim}{rdelim};
                window.chartsData["{$element->id}_rated_music"] = {$element->getRatedYearsData('zxmusic')};
            </script>
        </div>
        <div class="stats_section">
            <h2 class="stats_playshistory_description">{translations name="stats.plays"}</h2>
            <canvas class="stats_chart chart_component" data-chartid="{$element->id}_playshistory" width="900"
                    height="200"></canvas>
            <script>
                window.chartsData = window.chartsData || {ldelim}{rdelim};
                window.chartsData["{$element->id}_playshistory"] = {$element->getPlaysHistoryData()};
            </script>
        </div>

        <div class="stats_section">
            <h2 class="stats_viewshistory_description">{translations name="stats.comments"}</h2>
            <canvas class="stats_chart chart_component" data-chartid="{$element->id}_commentshistory" width="900"
                    height="200"></canvas>
            <script>
                window.chartsData = window.chartsData || {ldelim}{rdelim};
                window.chartsData["{$element->id}_commentshistory"] = {$element->getCommentsHistoryData()};
            </script>
        </div>
        <div class="stats_section">
            <h2 class="stats_viewshistory_description">{translations name="stats.uploads"}</h2>
            <canvas class="stats_chart chart_component" data-chartid="{$element->id}_uploadshistory" width="900"
                    height="200"></canvas>
            <script>
                window.chartsData = window.chartsData || {ldelim}{rdelim};
                window.chartsData["{$element->id}_uploadshistory"] = {$element->getUploadsHistoryData()};
            </script>
        </div>
    </div>
    <div class="stats_sections">
        <div class="stats_section">
            <h2 class="stats_top_graphicsuploaders">{translations name="stats.top_graphicsuploaders"}</h2>
            <table class="table_component">
                {foreach from=$element->getTopWorksUsers('addZxPicture', 10) item=data}
                    <tr>
                        <td>{$data['count']}</td>
                        <td>{include file=$theme->template("component.username.tpl") userName=$data['user']->userName userUrl=$data['user']->getUrl() userType=$data['user']->getBadgetTypesString()}</td>
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
                        <td>{include file=$theme->template("component.username.tpl") userName=$data['user']->userName userUrl=$data['user']->getUrl() userType=$data['user']->getBadgetTypesString()}</td>
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
                        <td>{include file=$theme->template("component.username.tpl") userName=$data['user']->userName userUrl=$data['user']->getUrl() userType=$data['user']->getBadgetTypesString()}</td>
                    </tr>
                {/foreach}
            </table>
        </div>
        <div class="stats_section">
            <h2 class="stats_top_voters">{translations name="stats.top_voters"}</h2>
            <table class="table_component">
                {foreach from=$element->getTopVotesUsers(20) item=data}
                    <tr>
                        <td>{$data['count']}</td>
                        <td>{include file=$theme->template("component.username.tpl") userName=$data['user']->userName userUrl=$data['user']->getUrl() userType=$data['user']->getBadgetTypesString()}</td>
                    </tr>
                {/foreach}
            </table>
        </div>
        <div class="stats_section">
            <h2 class="stats_top_tagadders">{translations name="stats.top_tagadders"}</h2>
            <table class="table_component">
                {foreach from=$element->getTopWorksUsers('tagAdded', 20) item=data}
                    <tr>
                        <td>{$data['count']}</td>
                        <td>{include file=$theme->template("component.username.tpl") userName=$data['user']->userName userUrl=$data['user']->getUrl() userType=$data['user']->getBadgetTypesString()}</td>
                    </tr>
                {/foreach}
            </table>
        </div>
        <div class="stats_section">
            <h2 class="stats_top_tagadders">{translations name="stats.top_softuploaders"}</h2>
            <table class="table_component">
                {foreach from=$element->getTopWorksUsers('addZxProd', 10) item=data}
                    <tr>
                        <td>{$data['count']}</td>
                        <td>{include file=$theme->template("component.username.tpl") userName=$data['user']->userName userUrl=$data['user']->getUrl() userType=$data['user']->getBadgetTypesString()}</td>
                    </tr>
                {/foreach}
            </table>
        </div>
    </div>
{/capture}
{assign moduleClass "stats_block"}
{assign moduleTitleClass ""}
{assign moduleContentClass ""}

{include file=$theme->template("component.contentmodule.tpl")}