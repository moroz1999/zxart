{if $element->title}
	{capture assign="moduleTitle"}
		{$element->title}
	{/capture}
{/if}
{capture assign="moduleContent"}
	<div class='picture_details_top'>
		<div class='picture_details_main'>
			<img loading="lazy" rel="image_src" class='picture_details_main_image zxgallery_item{if $currentMode.mode != 'mix' && $element->isFlickering()} flicker_image{/if}' src='{$element->getImageUrl(1)}' alt='{$element->title}'/>
		</div>
		<div class='picture_details_main picture_details_main_2x'>
			<img loading="lazy" rel="image_src" class='picture_details_main_image zxgallery_item{if $currentMode.mode != 'mix' && $element->isFlickering()} flicker_image{/if}' src='{$element->getImageUrl(2)}' alt='{$element->title}'/>
		</div>
		<div class='picture_details_main picture_details_main_3x'>
			<img loading="lazy" rel="image_src" class='picture_details_main_image zxgallery_item{if $currentMode.mode != 'mix' && $element->isFlickering()} flicker_image{/if}' src='{$element->getImageUrl(3)}' alt='{$element->title}'/>
		</div>
	</div>
	<div class="picture_details_materials">
		{if $element->sequenceName != ""}
			<div class="picture_details_sequence">
				<img loading="lazy" class="picture_details_sequence_image stage_animation_gif" src='{$controller->baseURL}file/id:{$element->sequence}/filename:{$element->sequenceName}' alt="{$element->title} phases" />
				<a class="picture_details_sequence_link" href="{$controller->baseURL}file/id:{$element->sequence}/filename:{$element->sequenceName}">{translations name="label.stages_download"}</a>
			</div>
		{/if}
		{if $element->inspiredName != ""}
			<div class="picture_details_inspired">
				<img loading="lazy" class="picture_details_inspired_image" src='{$controller->baseURL}image/type:inspiredImage/id:{$element->inspired}/filename:{$element->inspiredName}' alt="{$element->title} inspiration" />
			</div>
		{/if}
		{if $element->inspired2Name != ""}
			<div class="picture_details_inspired">
				<img loading="lazy" class="picture_details_inspired_image" src='{$controller->baseURL}image/type:inspired2Image/id:{$element->inspired2}/filename:{$element->inspired2Name}' alt="{$element->title} another inspiration" />
			</div>
		{/if}
	</div>
	{include file=$theme->template("component.pictureinfo.tpl")}
	<div class="zxpicture_editing_controls editing_controls">
		{if isset($currentElementPrivileges.showPublicForm) && $currentElementPrivileges.showPublicForm==1}
			<a class="button" href="{$element->URL}id:{$element->id}/action:showPublicForm/">{translations name='picture.edit'}</a>
		{/if}
		{if isset($currentElementPrivileges.publicDelete) && $currentElementPrivileges.publicDelete}
			<a class="button delete_button" href="{$element->URL}id:{$element->id}/action:publicDelete/">{translations name='picture.delete'}</a>
		{/if}
	</div>
	<script type='text/javascript'>
	if (!window.galleryPictures) window.galleryPictures = [];
	window.galleryPictures.push({$element->id});
	</script>
	<script>
		if (!window.picturesList) window.picturesList = [];
		window.picturesList.push({$element->getJsonInfo()});

		if (!window.imageInfoIndex) window.imageInfoIndex = {ldelim}{rdelim};
		window.imageInfoIndex['{$element->id}'] = {ldelim}
			'smallImage': "{$element->getImageUrl(1,false, false)}",
			'largeImage': "{$element->getImageUrl(2)}",
			'detailsURL': '{$element->URL}',
			'title': "{$element->title|escape:'javascript'}",
			'id': '{$element->id}',
			'flickering': '{$element->isFlickering() && ($currentMode.mode!='mix')}'
		{rdelim};
	</script>
	{if isset($currentElementPrivileges.submitTags) && $currentElementPrivileges.submitTags == true}
		{include file=$theme->template("tags.form.tpl") element=$element}
	{/if}
	{include file=$theme->template('component.mentions.tpl')}
	{include file=$theme->template('component.comments.tpl')}
	{if $element->denyComments}<p>{translations name="zxitem.commentsdenied"}</p>{/if}

	{include file=$theme->template('component.voteslist.tpl')}
	{if $element->denyVoting}<p>{translations name="zxitem.votingdenied"}</p>{/if}
	{stripdomspaces}
		{if $element->getReleaseElement()}
			{assign bestPictures $element->getReleaseElement()->getBestPictures(3, $element->id)}
			{if $bestPictures}
			<div class="picture_details_more_game gallery_pictures">
				<h2>{translations name="picture.morefromgame"}</h2>
				{include file=$theme->template('component.pictureslist.tpl') pictures=$bestPictures}
			</div>
			{/if}
		{else}
			{assign bestPictures $element->getBestAuthorsPictures(3)}
			{if $bestPictures}
			<div class="picture_details_more_author gallery_pictures">
				<h2>{translations name="picture.morefromauthor"}</h2>
				{include file=$theme->template('component.pictureslist.tpl') pictures=$bestPictures}
			</div>
			{/if}
		{/if}
	{/stripdomspaces}
{/capture}
{assign moduleClass "picture_details_block"}
{assign moduleTitleClass ""}
{assign moduleContentClass ""}
{include file=$theme->template("component.contentmodule.tpl")}