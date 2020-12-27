{if isset($pager)}
    <div class='zxpictures_top_controls'>
        {include file=$theme->template("pager.tpl") pager=$pager}
    </div>
{/if}
<div class="zxpictures_list gallery_pictures">
	{foreach from=$pictures item=element}{include file=$theme->template("zxPicture.short.tpl")}{/foreach}
</div>
{if isset($pager)}
    <div class='zxpictures_bottom_controls'>
        {include file=$theme->template("pager.tpl") pager=$pager}
    </div>
{/if}