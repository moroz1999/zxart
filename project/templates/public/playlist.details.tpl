{if $element->title}
    {capture assign="moduleTitle"}
        {$element->title}
    {/capture}
{/if}
{capture assign="moduleContent"}
    {if $element->getPicturesList()}
        <div class='playlist_details_pictures'>
            <div id="gallery_{$element->id}">
                {include file=$theme->template('component.pictureslist.tpl') pictures=$element->getPicturesList() pager=false}
            </div>
        </div>
    {/if}
    {if $element->getMusicList()}
        <div class='playlist_details_music'>
            {include file=$theme->template("component.musictable.tpl") musicList=$element->getMusicList() element=$element showplaylists=false showYear=false}
        </div>
    {/if}
    {if $prodsData = $element->getZxProdsListData()}
        <script>
            window.elementsData = window.elementsData ? window.elementsData : { };
            window.elementsData[{$element->id}] = {$prodsData};
        </script>
        <zx-prods-list element-id="{$element->id}" property="prods"></zx-prods-list>
    {/if}
{/capture}
{assign moduleClass "playlist_details"}
{assign moduleTitleClass ""}
{assign moduleContentClass ""}

{include file=$theme->template("component.contentmodule.tpl")}