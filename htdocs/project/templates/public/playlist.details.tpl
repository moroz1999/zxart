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
	{if $prods = $element->getZxProdsList()}
		<div class="playlist_details_prods zxprods_list">
			{foreach $prods as $prod}
				{include file=$theme->template('zxProd.short.tpl') element=$prod}
			{/foreach}
		</div>
	{/if}
{/capture}
{assign moduleClass "playlist_details"}
{assign moduleTitleClass ""}
{assign moduleContentClass ""}

{include file=$theme->template("component.contentmodule.tpl")}