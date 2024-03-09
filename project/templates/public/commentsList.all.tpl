{if $element->title}
	{capture assign="moduleTitle"}
		{$element->title}
	{/capture}
{/if}
{capture assign="moduleContent"}
	<div class="comments_list_controls">
		{include file=$theme->template("pager.tpl") pager=$element->getPager()}
	</div>
	<div class='comments_list gallery_pictures' id="gallery_{$element->id}">
	{foreach from=$element->getCommentsList() item=comment}
		{$target = $comment->getInitialTarget()}
		{if $target && $target->structureType == 'zxPicture'}
			{include file=$theme->template("comment.picture.tpl") galleryId=$element->id element=$comment displaySubComments=false}
		{elseif $target && $target->structureType == 'zxMusic'}
			{include file=$theme->template("comment.music.tpl") element=$comment displaySubComments=false}
		{elseif $target && $target->structureType == 'zxProd'}
			{include file=$theme->template("comment.zxProd.tpl") element=$comment displaySubComments=false}
		{else}
			{include file=$theme->template("comment.full.tpl") element=$comment displaySubComments=false displayTarget=true}
		{/if}
	{/foreach}
	</div>
	<div class="comments_list_controls">
		{include file=$theme->template("pager.tpl") pager=$element->getPager()}
	</div>
{/capture}
{assign moduleClass ""}
{assign moduleTitleClass ""}
{assign moduleContentClass ""}

{include file=$theme->template("component.contentmodule.tpl")}
