{if $element->title}
	{capture assign="moduleTitle"}
		{$element->title}
	{/capture}
{/if}
{capture assign="moduleContent"}

	{if $element->isPlayable()}
		<div class="music_details_controls">
			<div class="music_controls_short elementid_{$element->id}"></div><span class="music_controls_label">{translations name="zxmusic.play"}</span>
		</div>
		<script>
			if (!window.musicList) window.musicList = [];
			window.musicList.push({$element->getJsonInfo()});
		</script>
	{/if}
	{if $element->embedCode}
		<div class="music_details_embed">
			{$element->embedCode}
		</div>
	{/if}

	{include file=$theme->template("component.musicinfo.tpl")}
	<div class="music_editing_controls editing_controls">
		{if isset($currentElementPrivileges.showPublicForm) && $currentElementPrivileges.showPublicForm}
			<a class="button" href="{$element->URL}id:{$element->id}/action:showPublicForm/">{translations name='zxmusic.edit'}</a>
		{/if}
		{if isset($currentElementPrivileges.publicDelete) && $currentElementPrivileges.publicDelete}
			<a class="button delete_button" href="{$element->URL}id:{$element->id}/action:publicDelete/">{translations name='zxmusic.delete'}</a>
		{/if}
	</div>

	{if isset($currentElementPrivileges.submitTags) && $currentElementPrivileges.submitTags == true}
		{include file=$theme->template("tags.form.tpl") element=$element}
	{/if}
	{if $element->denyPlaying}<p>{translations name="zxitem.playingdenied"}</p>{/if}
	{include file=$theme->template('component.comments.tpl')}
	{if $element->denyComments}<p>{translations name="zxitem.commentsdenied"}</p>{/if}

	{include file=$theme->template('component.voteslist.tpl')}
	{if $element->denyVoting}<p>{translations name="zxitem.votingdenied"}</p>{/if}
{/capture}
{assign moduleClass "music_details_block"}
{assign moduleTitleClass ""}
{assign moduleContentClass ""}

{include file=$theme->template("component.contentmodule.tpl")}
