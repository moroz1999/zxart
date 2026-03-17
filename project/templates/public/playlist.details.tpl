{if $element->title}
    {capture assign="moduleTitle"}
        {$element->title}
    {/capture}
{/if}
{capture assign="moduleContent"}
    {if $element->getPicturesList()}
        <div class='playlist_details_pictures'>
            <zx-pictures-list element-id="{$element->id}"></zx-pictures-list>
        </div>
    {/if}
    <div class='playlist_details_music'>
        <zx-music-list element-id="{$element->id}"></zx-music-list>
    </div>
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
